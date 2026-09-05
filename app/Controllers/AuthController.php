<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\UserRepository;
use PDOException;

final class AuthController
{
    public function __construct(private readonly UserRepository $users, private readonly RateLimiter $limiter) {}

    public function loginPage(Request $request): Response
    {
        if(Auth::user())return Response::redirect(safe_next($request->query['next']??null));
        return Response::html(View::render('auth/login',['title'=>'Sign in — Genezenz Pharmacy','next'=>safe_next($request->query['next']??null)]));
    }
    public function registerPage(): Response { if(Auth::user())return Response::redirect('/profile');return Response::html(View::render('auth/register',['title'=>'Create account — Genezenz Pharmacy'])); }
    public function login(Request $request): Response
    {
        if(!Csrf::check((string)($request->form['_token']??'')))return $this->back('Your form expired. Please retry.','/login');
        if(!$this->limiter->allow('login',$request->ip().'|'.strtolower((string)($request->form['email']??'')),5,900))return $this->back('Too many attempts. Try again in 15 minutes.','/login',429);
        $email=filter_var(trim((string)($request->form['email']??'')),FILTER_VALIDATE_EMAIL);$password=(string)($request->form['password']??'');
        if(!$email||!Auth::attempt((string)$email,$password,$request->ip(),$_SERVER['HTTP_USER_AGENT']??''))return $this->back('Email or password is incorrect.','/login',422);
        unset($_SESSION['_old']); return Response::redirect(safe_next($request->form['next']??null));
    }
    public function register(Request $request): Response
    {
        if(!Csrf::check((string)($request->form['_token']??'')))return $this->back('Your form expired. Please retry.','/register');
        if(!$this->limiter->allow('register',$request->ip(),4,3600))return $this->back('Too many registrations. Please try later.','/register',429);
        $name=trim((string)($request->form['name']??''));$email=filter_var(trim((string)($request->form['email']??'')),FILTER_VALIDATE_EMAIL);$phone=preg_replace('/\D+/','',(string)($request->form['phone']??''))??'';$password=(string)($request->form['password']??'');
        $_SESSION['_old']=['name'=>$name,'email'=>(string)$email,'phone'=>$phone];
        if(strlen($name)<2||!$email||strlen($password)<10||!preg_match('/[A-Za-z]/',$password)||!preg_match('/\d/',$password))return $this->back('Use a valid name/email and a password of 10+ characters with letters and numbers.','/register',422);
        try{$id=$this->users->create($name,(string)$email,$password,$phone);Auth::issue($id,'CUSTOMER',$request->ip(),$_SERVER['HTTP_USER_AGENT']??'');unset($_SESSION['_old']);return Response::redirect('/profile');}
        catch(PDOException $e){$message=$e->getCode()==='23000'?'An account already exists for this email.':'Account could not be created.';return $this->back($message,'/register',422);}
        catch(\Throwable){return $this->back('Account service is unavailable until MySQL is configured.','/register',503);}
    }
    public function logout(Request $request): Response { if(Csrf::check((string)($request->form['_token']??'')))Auth::logout();return Response::redirect('/'); }
    private function back(string $message,string $path,int $status=400):Response { flash('error',$message);return new Response('', $status, ['Location'=>$path]); }
}
