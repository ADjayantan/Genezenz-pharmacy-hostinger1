<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\RateLimiter;
use App\Repositories\LeadRepository;
use Throwable;

final class LeadController
{
    public function __construct(private readonly LeadRepository $leads, private readonly RateLimiter $limiter)
    {
    }

    public function store(Request $request): Response
    {
        $input = $request->input();
        $token = (string) ($input['_token'] ?? $request->headers['x-csrf-token'] ?? '');
        if (!Csrf::check($token)) {
            return Response::json(['message' => 'Your session expired. Refresh the page and try again.'], 419);
        }
        if (!$this->limiter->allow('lead', $request->ip(), 6, 3600)) {
            return Response::json(['message' => 'Too many requests. Please call the pharmacy instead.'], 429);
        }

        if (trim((string) ($input['website'] ?? '')) !== '') {
            return Response::json(['message' => 'We will call you back shortly.']);
        }

        $name = trim((string) ($input['name'] ?? ''));
        $phone = preg_replace('/[^0-9+]/', '', (string) ($input['phone'] ?? '')) ?? '';
        $message = trim((string) ($input['message'] ?? ''));

        $nameLength = function_exists('mb_strlen') ? \mb_strlen($name) : strlen($name);
        $messageLength = function_exists('mb_strlen') ? \mb_strlen($message) : strlen($message);
        if ($nameLength < 2 || $nameLength > 80) {
            return Response::json(['message' => 'Enter a valid name.'], 422);
        }
        if (!preg_match('/^(?:\+?91)?[6-9][0-9]{9}$/', $phone)) {
            return Response::json(['message' => 'Enter a valid Indian mobile number.'], 422);
        }
        if ($messageLength > 1000) {
            return Response::json(['message' => 'Message is too long.'], 422);
        }

        try {
            $this->leads->createWebsiteLead(['name' => $name, 'phone' => $phone, 'message' => $message]);
        } catch (Throwable $error) {
            error_log('Lead capture failed: ' . $error->getMessage());
            return Response::json(['message' => 'We could not save the request. Please call the pharmacy instead.'], 503);
        }

        return Response::json(['message' => 'Request received. Our pharmacist will call you shortly.'], 201);
    }
}
