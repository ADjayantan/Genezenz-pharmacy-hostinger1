<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\ProductRepository;

final class ProductsController
{
    public function __construct(private readonly ProductRepository $products, private readonly ?\App\Core\RateLimiter $limiter = null)
    {
    }

    public function index(Request $request): Response
    {
        $query = trim((string) ($request->query['q'] ?? ''));
        $category = trim((string) ($request->query['cat'] ?? ''));
        $availability = trim((string) ($request->query['availability'] ?? ''));
        $type = trim((string) ($request->query['type'] ?? ''));
        $sort = trim((string) ($request->query['sort'] ?? 'name'));
        $page = max(1, (int) ($request->query['page'] ?? 1));
        $all = array_values(array_filter($this->products->catalogue($query, $category), static function(array $product) use($availability,$type):bool {
            if($availability==='in-stock' && (int)$product['stock']<1)return false;
            if($type==='rx' && empty($product['rx_required']))return false;
            if($type==='otc' && !empty($product['rx_required']))return false;
            return true;
        }));
        usort($all, static function(array $a,array $b) use($sort):int{return match($sort){'price-low'=>(float)$a['price']<=>(float)$b['price'],'price-high'=>(float)$b['price']<=>(float)$a['price'],'stock'=>(int)$b['stock']<=>(int)$a['stock'],default=>strcasecmp((string)$a['name'],(string)$b['name'])};});
        $perPage=24;$total=count($all);$pages=max(1,(int)ceil($total/$perPage));$page=min($page,$pages);

        return Response::html(View::render('products/index', [
            'title' => 'Medicines & Healthcare Products | Genezenz Pharmacy',
            'description' => 'Browse medicines, vitamins, baby care and healthcare products available from Genezenz Pharmacy in Coimbatore.',
            'canonical' => app_url('/products'),
            'products' => array_slice($all,($page-1)*$perPage,$perPage),
            'categories' => $this->products->categories(),
            'query' => $query,
            'category' => $category,
            'availability'=>$availability,'type'=>$type,'sort'=>$sort,'page'=>$page,'pages'=>$pages,'total'=>$total,
        ]));
    }

    public function show(Request $request): Response
    {
        $product = $this->products->findBySlug($request->params['slug'] ?? '');
        if (!$product) {
            return Response::html(View::render('errors/404', [
                'title' => 'Medicine not found',
                'description' => 'That medicine is not available in the public catalogue.',
            ]), 404);
        }

        return Response::html(View::render('products/show', [
            'title' => ($product['meta_title'] ?? null) ?: $product['name'] . ' | Genezenz Pharmacy',
            'description' => ($product['meta_description'] ?? null) ?: $product['description'],
            'canonical' => app_url('/products/' . $product['slug']),
            'product' => $product,
        ]));
    }

    public function search(Request $request): Response
    {
        $query = trim((string) ($request->query['q'] ?? ''));
        if (strlen($query) < 2 || strlen($query) > 100) return Response::json(['matches' => [], 'related' => []]);
        if ($this->limiter && !$this->limiter->allow('search', $request->ip(), 90, 60)) return Response::json(['message' => 'Search limit reached. Please try again shortly.'], 429);
        $records = $this->products->search($query);
        $map = static fn (array $product): array => [
            'id' => (string) $product['id'],
            'name' => $product['name'],
            'slug' => $product['slug'],
            'price' => (float) $product['price'],
            'brand' => $product['brand'],
            'stock' => (int) $product['stock'],
            'rxRequired' => (bool) $product['rx_required'],
        ];

        return Response::json(['matches' => array_map($map, $records), 'related' => array_map($map, $this->products->relatedToSearch($records))]);
    }
}
