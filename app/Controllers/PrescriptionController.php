<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;use App\Core\Csrf;use App\Core\RateLimiter;use App\Core\Request;use App\Core\Response;use App\Core\View;use App\Repositories\PrescriptionRepository;

final class PrescriptionController
{
    public function __construct(private readonly PrescriptionRepository $prescriptions,private readonly RateLimiter $limiter){}
    public function page(Request $request):Response{if(!Auth::user())return Response::redirect('/login?next=/upload-prescription');return Response::html(View::render('shop/upload',['title'=>'Upload prescription — Genezenz Pharmacy']));}
    public function store(Request $request):Response{$user=Auth::user();if(!$user)return Response::redirect('/login?next=/upload-prescription');if(!Csrf::check((string)($request->form['_token']??'')))return $this->error('Your form expired.');if(!$this->limiter->allow('upload',$request->ip().'|'.$user['id'],8,3600))return $this->error('Upload limit reached. Try later.',429);try{$this->prescriptions->store((int)$user['id'],$_FILES['prescription']??[],['patient_name'=>trim((string)($request->form['patient_name']??'')),'doctor_name'=>trim((string)($request->form['doctor_name']??'')),'notes'=>trim((string)($request->form['notes']??''))]);flash('success','Prescription securely uploaded for pharmacist review.');return Response::redirect('/profile');}catch(\Throwable$e){return $this->error($e->getMessage(),422);}}
    public function file(Request $request):Response{$user=Auth::user();if(!$user)return Response::html('',404);$file=$this->prescriptions->file((int)$request->params['id'],(int)$user['id'],Auth::isAdmin());if(!$file)return Response::html('',404);$safe=preg_replace('/[^A-Za-z0-9._-]/','_',basename($file['name']))?:'prescription';return new Response($file['bytes'],200,['Content-Type'=>$file['mime'],'Content-Disposition'=>'inline; filename="'.$safe.'"','Cache-Control'=>'private, no-store','X-Content-Type-Options'=>'nosniff','Content-Security-Policy'=>"default-src 'none'; sandbox"]);}
    private function error(string $message,int $status=400):Response{flash('error',$message);return new Response('', $status, ['Location'=>'/upload-prescription']);}
}
