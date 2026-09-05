<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\ProductRepository;

final class HomeController
{
    public function __construct(private readonly ProductRepository $products)
    {
    }

    public function __invoke(Request $request): Response
    {
        return Response::html(View::render('home/index', [
            'title' => 'Online Pharmacy in Coimbatore — Medicine Delivery | Genezenz Pharmacy',
            'description' => 'Buy genuine medicines online in Coimbatore. Upload your prescription, get pharmacist-verified medicines delivered same day. Pharmacy in Ganapathy since 2014.',
            'canonical' => app_url('/'),
            'products' => $this->products->popular(),
            'categories' => $this->products->categories(),
        ]));
    }
}
