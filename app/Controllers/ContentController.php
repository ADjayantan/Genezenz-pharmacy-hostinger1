<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;use App\Core\Response;use App\Core\View;use App\Repositories\ProductRepository;

final class ContentController
{
    public function __construct(private readonly ProductRepository $products) {}
    public function robots(): Response
    {
        $enabled = \App\Core\Env::get('APP_ENV', 'production') === 'production'
            && filter_var(\App\Core\Env::get('SEO_INDEXING_ENABLED', 'false'), FILTER_VALIDATE_BOOL);
        $body = "User-agent: *\nAllow: /\n";
        if ($enabled && filter_var(app_url('/'), FILTER_VALIDATE_URL)) $body .= "\nSitemap: ".app_url('/sitemap.xml')."\n";
        return new Response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }
    public function page(Request $request):Response{$pages=require BASE_PATH.'/app/Config/content.php';$key=trim($request->path,'/');if(!isset($pages[$key]))return Response::html(View::render('errors/404',['title'=>'Page not found']),404);return Response::html(View::render('content/page',['title'=>$pages[$key][0].' — Genezenz Pharmacy','heading'=>$pages[$key][0],'eyebrow'=>$pages[$key][1],'paragraphs'=>array_slice($pages[$key],2)]));}
    public function legalIndex():Response{$legal=require BASE_PATH.'/app/Config/legal.php';return Response::html(View::render('content/legal-index',['title'=>'Legal & pharmacy policies — Genezenz Pharmacy','policies'=>$legal]));}
    public function legal(Request $request):Response{$legal=require BASE_PATH.'/app/Config/legal.php';$slug=(string)$request->params['slug'];if(!isset($legal[$slug]))return Response::html(View::render('errors/404',['title'=>'Policy not found']),404);return Response::html(View::render('content/page',['title'=>$legal[$slug][0].' — Genezenz Pharmacy','heading'=>$legal[$slug][0],'eyebrow'=>'Legal & pharmacy policy','paragraphs'=>array_slice($legal[$slug],1)]));}
    public function area(Request $request):Response{$site=require BASE_PATH.'/app/Config/site.php';$slug=(string)$request->params['area'];$area=null;foreach($site['service_areas'] as $candidate)if(slugify($candidate)===$slug)$area=$candidate;if(!$area)return Response::html(View::render('errors/404',['title'=>'Area not found']),404);return Response::html(View::render('content/area',['title'=>'Online pharmacy in '.$area.', Coimbatore — Genezenz','area'=>$area]));}
    public function sitemap():Response{$site=require BASE_PATH.'/app/Config/site.php';$legal=require BASE_PATH.'/app/Config/legal.php';$urls=['/','/products','/about','/contact','/insurance','/legal','/upload-prescription'];foreach(array_keys($legal)as$slug)$urls[]='/legal/'.$slug;foreach($site['service_areas']as$area)$urls[]='/pharmacy-in-'.slugify($area).'-coimbatore';foreach($this->products->catalogue()as$product)$urls[]='/products/'.$product['slug'];$xml='<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';foreach(array_unique($urls)as$url)$xml.='<url><loc>'.e(app_url($url)).'</loc></url>';$xml.='</urlset>';return new Response($xml,200,['Content-Type'=>'application/xml; charset=UTF-8','Cache-Control'=>'public, max-age=3600']);}
}
