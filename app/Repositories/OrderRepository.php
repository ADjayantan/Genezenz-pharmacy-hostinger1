<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use RuntimeException;

final class OrderRepository
{
    public function __construct(private readonly ?PDO $database) {}

    /** @param array<int, array{id:int,qty:int}> $lines @param array<string,string> $customer */
    public function create(int $userId, array $lines, array $customer, ?int $prescriptionId = null): string
    {
        if (!$this->database) throw new RuntimeException('Database is not configured.');
        if ($lines === [] || count($lines) > 50) throw new RuntimeException('Your cart is empty or too large.');
        $merged = [];
        foreach ($lines as $line) {
            $id = (int) ($line['id'] ?? 0); $qty = (int) ($line['qty'] ?? 0);
            if ($id < 1 || $qty < 1 || $qty > 20) throw new RuntimeException('Invalid cart quantity.');
            $merged[$id] = min(20, ($merged[$id] ?? 0) + $qty);
        }
        ksort($merged, SORT_NUMERIC);
        $this->database->beginTransaction();
        try {
            $products = [];
            $read = $this->database->prepare('SELECT id,name,price,cost_price,gst_rate,stock,rx_required FROM products WHERE id=:id AND published=1 FOR UPDATE');
            foreach ($merged as $id => $qty) {
                $read->execute(['id' => $id]); $product = $read->fetch();
                if (!$product || (int) $product['stock'] < $qty) throw new RuntimeException('One or more products are unavailable in the requested quantity.');
                $product['qty'] = $qty; $products[] = $product;
            }
            $subtotal = array_reduce($products, fn(float $sum, array $p): float => $sum + ((float) $p['price'] * (int) $p['qty']), 0.0);
            $requiresRx = count(array_filter($products, static fn(array $p): bool => (bool) $p['rx_required'])) > 0;
            if ($requiresRx && !$prescriptionId) {
                throw new RuntimeException('Upload or select a prescription for the prescription-only items in your cart.');
            }
            if ($prescriptionId) {
                $rx = $this->database->prepare('SELECT id, user_id, order_id, status FROM prescriptions WHERE id=:id FOR UPDATE');
                $rx->execute(['id' => $prescriptionId]);
                $record = $rx->fetch();
                if (!$record || (int) $record['user_id'] !== $userId || $record['order_id'] !== null || $record['status'] === 'REJECTED') {
                    throw new RuntimeException('Choose an unused, non-rejected prescription from your own account.');
                }
            }
            $delivery = $subtotal >= 499 ? 0.0 : 40.0;
            $total = $subtotal + $delivery;
            $costs = array_filter(array_map(fn(array $p): ?float => $p['cost_price'] === null ? null : (float) $p['cost_price'] * (int) $p['qty'], $products), fn($v) => $v !== null);
            $costTotal = count($costs) === count($products) ? array_sum($costs) : null;
            $orderNo = 'GZ' . gmdate('ymd') . strtoupper(bin2hex(random_bytes(3)));
            $insert = $this->database->prepare('INSERT INTO orders(order_no,user_id,total,cost_total,name,phone,address,pincode,notes,payment_method) VALUES(:no,:user,:total,:cost,:name,:phone,:address,:pincode,:notes,\'COD\')');
            $insert->execute(['no'=>$orderNo,'user'=>$userId,'total'=>number_format($total,2,'.',''),'cost'=>$costTotal,'name'=>$customer['name'],'phone'=>$customer['phone'],'address'=>$customer['address'],'pincode'=>$customer['pincode'],'notes'=>$customer['notes'] ?: null]);
            $orderId = (int) $this->database->lastInsertId();
            $this->database->prepare('UPDATE orders SET requires_prescription=:rx WHERE id=:id')
                ->execute(['rx' => (int)($requiresRx || $prescriptionId !== null), 'id' => $orderId]);
            if ($prescriptionId) {
                $this->database->prepare('UPDATE prescriptions SET order_id=:order_id WHERE id=:id')
                    ->execute(['order_id' => $orderId, 'id' => $prescriptionId]);
            }
            $item = $this->database->prepare('INSERT INTO order_items(order_id,product_id,name,quantity,price,cost_price,gst_rate) VALUES(:order,:product,:name,:qty,:price,:cost,:gst)');
            $stock = $this->database->prepare('UPDATE products SET stock=stock-:qty_decrement WHERE id=:id AND stock>=:qty_check');
            foreach ($products as $p) {
                $stock->execute(['qty_decrement'=>$p['qty'],'qty_check'=>$p['qty'],'id'=>$p['id']]);
                if ($stock->rowCount() !== 1) throw new RuntimeException('Stock changed while placing the order. Please retry.');
                $item->execute(['order'=>$orderId,'product'=>$p['id'],'name'=>$p['name'],'qty'=>$p['qty'],'price'=>$p['price'],'cost'=>$p['cost_price'],'gst'=>$p['gst_rate'] ?? 5]);
            }
            $this->database->prepare("INSERT INTO order_events(order_id,status,note) VALUES(:id,'PENDING','Order placed by customer')")->execute(['id'=>$orderId]);
            $this->database->commit();
            return $orderNo;
        } catch (\Throwable $error) {
            if ($this->database->inTransaction()) $this->database->rollBack();
            throw $error;
        }
    }

    /** @return array<int, array<string,mixed>> */
    public function forUser(int $userId): array
    {
        if (!$this->database) return [];
        $s=$this->database->prepare('SELECT order_no,total,status,created_at FROM orders WHERE user_id=:user ORDER BY created_at DESC'); $s->execute(['user'=>$userId]); return $s->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public function find(string $orderNo, ?int $userId = null): ?array
    {
        if (!$this->database) return null;
        $sql='SELECT * FROM orders WHERE order_no=:no' . ($userId ? ' AND user_id=:user' : '') . ' LIMIT 1';
        $s=$this->database->prepare($sql); $params=['no'=>$orderNo]; if($userId) $params['user']=$userId; $s->execute($params); $order=$s->fetch();
        if(!is_array($order)) return null;
        $i=$this->database->prepare('SELECT name,quantity,price,gst_rate FROM order_items WHERE order_id=:id'); $i->execute(['id'=>$order['id']]); $order['items']=$i->fetchAll();
        $e=$this->database->prepare('SELECT status,note,actor,created_at FROM order_events WHERE order_id=:id ORDER BY created_at'); $e->execute(['id'=>$order['id']]); $order['events']=$e->fetchAll();
        return $order;
    }

    /** @return array<int,array<string,mixed>> */
    public function all(string $status=''): array
    {
        if (!$this->database) return [];
        $sql='SELECT o.*,u.email FROM orders o JOIN users u ON u.id=o.user_id' . ($status ? ' WHERE o.status=:status' : '') . ' ORDER BY o.created_at DESC LIMIT 300';
        $s=$this->database->prepare($sql); $s->execute($status ? ['status'=>$status] : []); return $s->fetchAll();
    }

    public function updateStatus(string $orderNo, string $status, string $actor, array $tracking=[]): void
    {
        if (!$this->database) throw new RuntimeException('Database is not configured.');
        $allowed=['PENDING','CONFIRMED','PACKED','SHIPPED','DELIVERED','CANCELLED'];
        if(!in_array($status,$allowed,true)) throw new RuntimeException('Invalid status.');
        $this->database->beginTransaction();
        try {
            $s=$this->database->prepare('SELECT id,status,stock_restored_at,requires_prescription FROM orders WHERE order_no=:no FOR UPDATE'); $s->execute(['no'=>$orderNo]); $order=$s->fetch();
            if(!$order) throw new RuntimeException('Order not found.');
            if($order['status']==='CANCELLED' && $status!=='CANCELLED') throw new RuntimeException('Cancelled orders cannot be reopened.');
            if ($order['status'] === 'DELIVERED' && $status !== 'DELIVERED') {
                throw new RuntimeException('Delivered orders cannot be reopened or cancelled. Handle returns separately.');
            }
            if (in_array($status, ['CONFIRMED', 'PACKED', 'SHIPPED', 'DELIVERED'], true)) {
                $rxItems = $this->database->prepare('SELECT COUNT(*) FROM order_items i JOIN products p ON p.id=i.product_id WHERE i.order_id=:id AND p.rx_required=1');
                $rxItems->execute(['id' => $order['id']]);
                $linkedRx = $this->database->prepare('SELECT status FROM prescriptions WHERE order_id=:id FOR UPDATE');
                $linkedRx->execute(['id' => $order['id']]);
                $reviews = $linkedRx->fetchAll(PDO::FETCH_COLUMN);
                if (((bool)$order['requires_prescription'] || (int) $rxItems->fetchColumn() > 0 || $reviews !== []) && ($reviews === [] || count(array_filter($reviews, static fn(string $review): bool => $review !== 'APPROVED')) > 0)) {
                    throw new RuntimeException('A pharmacist must approve the linked prescription before this order can progress.');
                }
            }
            $tracking += ['courier' => '', 'tracking_id' => '', 'tracking_url' => '', 'expected_at' => '', 'note' => ''];
            if ($tracking['tracking_url'] !== '' && (!filter_var($tracking['tracking_url'], FILTER_VALIDATE_URL) || !in_array(strtolower((string) parse_url($tracking['tracking_url'], PHP_URL_SCHEME)), ['http', 'https'], true))) {
                throw new RuntimeException('Tracking URL must be a valid HTTP or HTTPS address.');
            }
            if($status==='CANCELLED' && !$order['stock_restored_at']) {
                $items=$this->database->prepare('SELECT product_id,quantity FROM order_items WHERE order_id=:id'); $items->execute(['id'=>$order['id']]);
                $restore=$this->database->prepare('UPDATE products SET stock=stock+:qty WHERE id=:id'); foreach($items->fetchAll() as $line) $restore->execute(['qty'=>$line['quantity'],'id'=>$line['product_id']]);
            }
            $u=$this->database->prepare('UPDATE orders SET status=:status,courier=:courier,tracking_id=:tracking,tracking_url=:url,expected_at=:expected,delivered_at=IF(:delivered_status=\'DELIVERED\',UTC_TIMESTAMP(),delivered_at),cancelled_at=IF(:cancelled_status=\'CANCELLED\',UTC_TIMESTAMP(),cancelled_at),stock_restored_at=IF(:restored_status=\'CANCELLED\' AND stock_restored_at IS NULL,UTC_TIMESTAMP(),stock_restored_at) WHERE id=:id');
            $u->execute(['status'=>$status,'delivered_status'=>$status,'cancelled_status'=>$status,'restored_status'=>$status,'courier'=>$tracking['courier']?:null,'tracking'=>$tracking['tracking_id']?:null,'url'=>$tracking['tracking_url']?:null,'expected'=>$tracking['expected_at']?:null,'id'=>$order['id']]);
            $this->database->prepare('INSERT INTO order_events(order_id,status,note,actor) VALUES(:id,:status,:note,:actor)')->execute(['id'=>$order['id'],'status'=>$status,'note'=>$tracking['note']?:null,'actor'=>$actor]);
            $this->database->commit();
        } catch(\Throwable $e) { if($this->database->inTransaction())$this->database->rollBack(); throw $e; }
    }
}
