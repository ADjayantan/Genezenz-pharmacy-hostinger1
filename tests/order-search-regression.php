<?php
declare(strict_types=1);

// Uses connection-local temporary tables; existing pharmacy data is never changed.
require dirname(__DIR__).'/app/bootstrap.php';
set_exception_handler(static function(Throwable $error):void { fwrite(STDERR,$error->getMessage()."\n"); exit(1); });
if ($path = getenv('TEST_ENV_FILE')) \App\Core\Env::load($path);
$db = \App\Core\Database::connection();
$schema = file_get_contents(BASE_PATH.'/database/schema.sql');
$schema = str_replace('CREATE TABLE IF NOT EXISTS', 'CREATE TEMPORARY TABLE', $schema);
$schema = preg_replace('/^\s*CONSTRAINT[^\n]*\n/m', '', $schema);
$schema = preg_replace('/,\s*\) ENGINE/', "\n) ENGINE", $schema);
$db->exec($schema);
$db->exec("INSERT INTO users(id,name,email,password_hash) VALUES(1,'Test owner','owner@example.invalid','disabled'),(2,'Test other','other@example.invalid','disabled')");
$db->exec("INSERT INTO categories(id,name,slug) VALUES(1,'Test Care','test-care')");
$db->exec("INSERT INTO products(id,category_id,name,slug,description,price,stock,rx_required,brand,salt_name) VALUES
 (1,1,'Test Rx','test-rx','Test only',100,50,1,'Test Brand','Test Salt'),
 (2,1,'Test OTC','test-otc','Test only',50,50,0,'Test Brand','Other Salt'),
 (3,1,'Related care','related-care','Test only',25,20,0,'Related Brand','Other Salt')");
$db->exec("INSERT INTO product_tags(product_id,tag) VALUES(1,'special-tag')");
$db->exec("INSERT INTO prescriptions(id,user_id,storage_key,mime_type,size_bytes,status) VALUES
 (1,1,'test-owned','image/png',1,'PENDING'),
 (2,2,'test-other','image/png',1,'APPROVED'),
 (3,1,'test-rejected','image/png',1,'REJECTED')");
$orders = new \App\Repositories\OrderRepository($db);
$products = new \App\Repositories\ProductRepository($db, []);
$customer = ['name'=>'Test owner','phone'=>'9000000000','address'=>'Test address, not a customer','pincode'=>'641006','notes'=>''];
$checks = 0;
function check(bool $ok, string $message): void {
    global $checks;
    if (!$ok) throw new RuntimeException('FAIL '.$message);
    $checks++;
    echo 'PASS '.$message."\n";
}
function rejected(callable $call, string $message): void {
    try { $call(); } catch (RuntimeException $error) {
        if ($error instanceof PDOException) throw $error;
        check(true, $message); return;
    }
    check(false, $message);
}
$rxCart = [['id'=>1,'qty'=>2,'price'=>0.01]];
rejected(fn()=>$orders->create(1,$rxCart,$customer), 'Rx checkout rejects missing prescription');
rejected(fn()=>$orders->create(1,$rxCart,$customer,2), 'Cross-user prescription rejected');
rejected(fn()=>$orders->create(1,$rxCart,$customer,3), 'Rejected prescription rejected');
check((int)$db->query('SELECT stock FROM products WHERE id=1')->fetchColumn()===50, 'Rejected requests preserve stock');
check((int)$db->query('SELECT COUNT(*) FROM orders')->fetchColumn()===0, 'Rejected requests leave no orders');
$order = $orders->create(1,$rxCart,$customer,1);
$stored = $orders->find($order,1);
check((float)$stored['total']===240.0, 'Server price overrides client price');
check((int)$db->query('SELECT stock FROM products WHERE id=1')->fetchColumn()===48, 'Order decrements stock');
check((int)$db->query('SELECT order_id FROM prescriptions WHERE id=1')->fetchColumn()===(int)$stored['id'], 'Prescription linked to created order');
rejected(fn()=>$orders->create(1,$rxCart,$customer,1), 'Used prescription cannot be reused');
rejected(fn()=>$orders->updateStatus($order,'CONFIRMED','test'), 'Pending Rx blocks confirmation');
rejected(fn()=>$orders->updateStatus($order,'SHIPPED','test'), 'Pending Rx blocks shipment');
$db->exec('UPDATE products SET rx_required=0 WHERE id=1');
rejected(fn()=>$orders->updateStatus($order,'PACKED','test'), 'Changing product Rx flag does not bypass order requirement');
$db->exec("UPDATE prescriptions SET status='APPROVED' WHERE id=1");
$orders->updateStatus($order,'SHIPPED','test');
check($orders->find($order,1)['status']==='SHIPPED', 'Approved Rx can progress');
$orders->updateStatus($order,'CANCELLED','test');
$orders->updateStatus($order,'CANCELLED','test');
check((int)$db->query('SELECT stock FROM products WHERE id=1')->fetchColumn()===50, 'Repeated cancellation restores stock only once');
$otc = $orders->create(1,[['id'=>2,'qty'=>1]],$customer);
$orders->updateStatus($otc,'DELIVERED','test');
rejected(fn()=>$orders->updateStatus($otc,'CANCELLED','test'), 'Delivered order cannot restore already delivered stock');
check(count($products->search('Test Brand',1))===1, 'Autocomplete obeys SQL limit');
check(count($products->search('Test Salt'))===1, 'Autocomplete searches composition');
check(count($products->search('special-tag'))===1, 'Autocomplete searches tags');
check(count($products->catalogue('special-tag'))===1, 'Tag suggestions and results page agree');
check(count($products->search('Test only'))===3 && count($products->catalogue('Test only'))===3, 'Description-only suggestions and results page agree');
check(count($products->search('Test Care'))===3, 'Autocomplete searches category');
check(count($products->catalogue('Test Care'))===3, 'Category suggestions and results page agree');
$related = $products->relatedToSearch($products->search('Test OTC'));
check(count($related)>0 && !in_array(2,array_map('intval',array_column($related,'id')),true), 'Related suggestions exclude original result');
check($products->search(str_repeat('x',101))===[], 'Oversized search rejected');
$db->exec('ALTER TABLE orders DROP COLUMN requires_prescription');
$db->exec(file_get_contents(BASE_PATH.'/database/migrations/001-order-prescription.sql'));
check($db->query("SHOW COLUMNS FROM orders LIKE 'requires_prescription'")->fetch()!==false, 'Existing-installation migration creates order requirement column');
check((int)$db->query("SELECT requires_prescription FROM orders WHERE id=".(int)$stored['id'])->fetchColumn()===1, 'Migration backfills linked prescription orders');
echo "Completed {$checks} database regression checks; temporary tables are discarded when the connection closes.\n";
