<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ProductRepository
{
    /** @param array{categories: array<int, array<string, mixed>>, products: array<int, array<string, mixed>>} $fallback */
    public function __construct(
        private readonly ?PDO $database,
        private readonly array $fallback
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function popular(int $limit = 8): array
    {
        if (!$this->database) {
            return array_slice($this->fallback['products'], 0, $limit);
        }

        $statement = $this->database->prepare(
            'SELECT p.id, p.name, p.slug, p.description, p.price, p.mrp, p.stock,
                    p.image_url, p.brand, p.salt_name, p.rx_required,
                    c.slug AS category_slug, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.published = 1 AND p.stock > 0
             ORDER BY p.stock DESC, p.name ASC
             LIMIT :limit'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public function categories(): array
    {
        if (!$this->database) {
            return $this->fallback['categories'];
        }

        $statement = $this->database->query(
            'SELECT c.id, c.name, c.slug, COUNT(p.id) AS product_count
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id AND p.published = 1
             GROUP BY c.id, c.name, c.slug
             ORDER BY c.name ASC'
        );
        return $statement->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public function catalogue(string $query = '', string $category = ''): array
    {
        if (!$this->database) {
            return array_values(array_filter(
                $this->fallback['products'],
                static function (array $product) use ($query, $category): bool {
                    $haystack = strtolower(implode(' ', array_filter([
                        $product['name'], $product['brand'], $product['salt_name'], $product['description'], $product['category_name'] ?? '', $product['category_slug'] ?? '',
                    ])));
                    $matchesQuery = $query === '' || str_contains($haystack, strtolower($query));
                    $matchesCategory = $category === '' || $product['category_slug'] === $category;
                    return $matchesQuery && $matchesCategory;
                }
            ));
        }

        $where = ['p.published = 1'];
        $params = [];
        if ($query !== '') {
            $where[] = '(p.name LIKE :query_name OR p.brand LIKE :query_brand OR p.salt_name LIKE :query_salt OR p.description LIKE :query_description OR c.name LIKE :query_category OR EXISTS (SELECT 1 FROM product_tags t WHERE t.product_id=p.id AND t.tag LIKE :query_tag))';
            $like = '%' . $query . '%';
            $params['query_name'] = $like;
            $params['query_brand'] = $like;
            $params['query_salt'] = $like;
            $params['query_description'] = $like;
            $params['query_category'] = $like;
            $params['query_tag'] = $like;
        }
        if ($category !== '') {
            $where[] = 'c.slug = :category';
            $params['category'] = $category;
        }

        $statement = $this->database->prepare(
            'SELECT p.id, p.name, p.slug, p.description, p.price, p.mrp, p.stock,
                    p.image_url, p.brand, p.salt_name, p.rx_required,
                    c.slug AS category_slug, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY p.name ASC
             LIMIT 200'
        );
        $statement->execute($params);
        return $statement->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        if (!$this->database) {
            foreach ($this->fallback['products'] as $product) {
                if ($product['slug'] === $slug) {
                    return $product;
                }
            }
            return null;
        }

        $statement = $this->database->prepare(
            'SELECT p.id, p.name, p.slug, p.description, p.content, p.price, p.mrp, p.stock,
                    p.image_url, p.brand, p.salt_name, p.rx_required, p.meta_title, p.meta_description,
                    c.slug AS category_slug, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.slug = :slug AND p.published = 1
             LIMIT 1'
        );
        $statement->execute(['slug' => $slug]);
        $product = $statement->fetch();
        return is_array($product) ? $product : null;
    }

    /** @return array<int, array<string, mixed>> */
    public function search(string $query, int $limit = 8): array
    {
        $query = trim($query);
        $length = function_exists('mb_strlen') ? \mb_strlen($query) : strlen($query);
        if ($length < 2) {
            return [];
        }

        if ($length > 100) return [];
        $limit = max(1, min(12, $limit));
        if (!$this->database) return array_slice($this->catalogue($query), 0, $limit);
        // Return only fields displayed by autocomplete; never materialise the whole catalogue.
        $s = $this->database->prepare(
            'SELECT p.id,p.name,p.slug,p.price,p.brand,p.stock,p.rx_required,c.slug category_slug,c.name category_name
             FROM products p LEFT JOIN categories c ON c.id=p.category_id
             WHERE p.published=1 AND
               (p.name LIKE :name OR p.brand LIKE :brand OR p.salt_name LIKE :salt OR p.description LIKE :description OR c.name LIKE :category
                OR EXISTS (SELECT 1 FROM product_tags t WHERE t.product_id=p.id AND t.tag LIKE :tag))
             ORDER BY (LOWER(p.name)=LOWER(:exact)) DESC, (p.name LIKE :prefix) DESC, (p.stock>0) DESC, p.name ASC
             LIMIT :limit'
        );
        foreach (['name','brand','salt','description','category','tag'] as $key) $s->bindValue(':'.$key, '%'.$query.'%');
        $s->bindValue(':exact', $query);
        $s->bindValue(':prefix', $query.'%');
        $s->bindValue(':limit', $limit, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll();
    }

    public function relatedToSearch(array $matches, int $limit = 3): array
    {
        if (!$this->database || $matches === []) return [];
        $categories = array_values(array_unique(array_filter(array_column($matches, 'category_slug'))));
        if ($categories === []) return [];
        $ids = array_map('intval', array_column($matches, 'id'));
        $sql = 'SELECT p.id,p.name,p.slug,p.price,p.brand,p.stock,p.rx_required FROM products p JOIN categories c ON c.id=p.category_id WHERE p.published=1 AND p.stock>0 AND p.rx_required=0 AND c.slug IN ('.implode(',', array_fill(0,count($categories),'?')).') AND p.id NOT IN ('.implode(',',array_fill(0,count($ids),'?')).') ORDER BY p.name LIMIT '.max(1,min(3,$limit));
        $s = $this->database->prepare($sql);
        $s->execute(array_merge($categories,$ids));
        return $s->fetchAll();
    }

    /** @return array<int,array<string,mixed>> */
    public function adminAll(): array
    {
        if (!$this->database) return $this->fallback['products'];
        return $this->database->query('SELECT p.*,c.name category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.updated_at DESC LIMIT 500')->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public function adminFind(int $id): ?array
    {
        if (!$this->database) return null;
        $s=$this->database->prepare('SELECT * FROM products WHERE id=:id');$s->execute(['id'=>$id]);$row=$s->fetch();return is_array($row)?$row:null;
    }

    /** @param array<string,mixed> $data */
    public function save(array $data, ?int $id=null): int
    {
        if(!$this->database)throw new \RuntimeException('Database is not configured.');
        $params=['category'=>$data['category_id']?:null,'name'=>$data['name'],'slug'=>$data['slug'],'description'=>$data['description'],'content'=>$data['content']?:null,'price'=>$data['price'],'mrp'=>$data['mrp']?:null,'cost'=>$data['cost_price']?:null,'stock'=>$data['stock'],'reorder'=>$data['reorder_level'],'batch'=>$data['batch_no']?:null,'expiry'=>$data['expiry_date']?:null,'gst'=>$data['gst_rate']?:null,'image'=>$data['image_url']?:null,'brand'=>$data['brand']?:null,'salt'=>$data['salt_name']?:null,'rx'=>$data['rx_required'],'published'=>$data['published'],'meta_title'=>$data['meta_title']?:null,'meta_description'=>$data['meta_description']?:null];
        if($id){$params['id']=$id;$sql='UPDATE products SET category_id=:category,name=:name,slug=:slug,description=:description,content=:content,price=:price,mrp=:mrp,cost_price=:cost,stock=:stock,reorder_level=:reorder,batch_no=:batch,expiry_date=:expiry,gst_rate=:gst,image_url=:image,brand=:brand,salt_name=:salt,rx_required=:rx,published=:published,meta_title=:meta_title,meta_description=:meta_description WHERE id=:id';}
        else{$sql='INSERT INTO products(category_id,name,slug,description,content,price,mrp,cost_price,stock,reorder_level,batch_no,expiry_date,gst_rate,image_url,brand,salt_name,rx_required,published,meta_title,meta_description) VALUES(:category,:name,:slug,:description,:content,:price,:mrp,:cost,:stock,:reorder,:batch,:expiry,:gst,:image,:brand,:salt,:rx,:published,:meta_title,:meta_description)';}
        $this->database->prepare($sql)->execute($params);return$id??(int)$this->database->lastInsertId();
    }

    public function remove(int $id): void
    {
        if(!$this->database)throw new \RuntimeException('Database is not configured.');
        $s=$this->database->prepare('SELECT COUNT(*) FROM order_items WHERE product_id=:id');$s->execute(['id'=>$id]);
        if((int)$s->fetchColumn()>0)$this->database->prepare('UPDATE products SET published=0 WHERE id=:id')->execute(['id'=>$id]);
        else$this->database->prepare('DELETE FROM products WHERE id=:id')->execute(['id'=>$id]);
    }
}
