<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;use App\Core\Csrf;use App\Core\RateLimiter;use App\Core\Request;use App\Core\Response;use App\Core\View;use App\Repositories\OrderRepository;use App\Repositories\PrescriptionRepository;

final class ShopController
{
    public function __construct(private readonly OrderRepository $orders,private readonly PrescriptionRepository $prescriptions,private readonly RateLimiter $limiter){}
    public function cart():Response{return Response::html(View::render('shop/cart',['title'=>'Your cart — Genezenz Pharmacy']));}
    public function checkout(Request $request):Response{if(!Auth::user())return Response::redirect('/login?next='.rawurlencode('/checkout'));return Response::html(View::render('shop/checkout',['title'=>'Checkout — Genezenz Pharmacy','user'=>Auth::user()]));}
    public function placeOrder(Request $request):Response
    {
        $user=Auth::user();if(!$user)return Response::json(['message'=>'Please sign in to place the order.'],401);
        $input=$request->json();if(!Csrf::check((string)($input['_token']??'')))return Response::json(['message'=>'Your form expired. Refresh and retry.'],419);
        if(!$this->limiter->allow('order',$request->ip().'|'.$user['id'],8,3600))return Response::json(['message'=>'Too many order attempts. Please call the pharmacy.'],429);
        $customer=['name'=>trim((string)($input['name']??'')),'phone'=>preg_replace('/\D+/','',(string)($input['phone']??''))??'','address'=>trim((string)($input['address']??'')),'pincode'=>preg_replace('/\D+/','',(string)($input['pincode']??''))??'','notes'=>trim((string)($input['notes']??''))];
        if(strlen($customer['name'])<2||strlen($customer['phone'])<10||strlen($customer['address'])<10||!preg_match('/^\d{6}$/',$customer['pincode']))return Response::json(['message'=>'Enter a valid delivery name, phone, address and 6-digit pincode.'],422);
        try{$orderNo=$this->orders->create((int)$user['id'],is_array($input['items']??null)?$input['items']:[],$customer);return Response::json(['message'=>'Order placed.','orderNo'=>$orderNo,'redirect'=>'/order/'.$orderNo],201);}catch(\Throwable$e){return Response::json(['message'=>$e->getMessage()],422);}
    }
    public function profile():Response{$user=Auth::user();if(!$user)return Response::redirect('/login?next=/profile');return Response::html(View::render('shop/profile',['title'=>'My account — Genezenz Pharmacy','user'=>$user,'orders'=>$this->orders->forUser((int)$user['id']),'prescriptions'=>$this->prescriptions->forUser((int)$user['id'])]));}
    public function order(Request $request):Response{$user=Auth::user();if(!$user)return Response::redirect('/login?next='.rawurlencode($request->path));$order=$this->orders->find((string)$request->params['orderNo'],(int)$user['id']);if(!$order)return Response::html(View::render('errors/404',['title'=>'Order not found']),404);return Response::html(View::render('shop/order',['title'=>'Order '.$order['order_no'].' — Genezenz Pharmacy','order'=>$order]));}
}
