<?php

declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Env;
use App\Core\Auth;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_url(string $path = '/'): string
{
    $base = rtrim(Env::get('APP_URL', ''), '/');
    $path = '/' . ltrim($path, '/');
    return $base !== '' ? $base . $path : $path;
}

function asset(string $path): string
{
    $path = ltrim($path, '/');
    $file = PUBLIC_PATH . '/assets/' . $path;
    $version = is_file($file) ? (string) filemtime($file) : '1';
    return app_url('/assets/' . $path) . '?v=' . rawurlencode($version);
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function money(float|int|string $value): string
{
    return '₹' . number_format((float) $value, 2, '.', ',');
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

function csp_nonce(): string
{
    return e($GLOBALS['csp_nonce'] ?? '');
}

/** @return array<string, mixed>|null */
function current_user(): ?array
{
    return Auth::user();
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }
    $message = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return is_string($message) ? $message : null;
}

function old(string $key, string $fallback = ''): string
{
    return e($_SESSION['_old'][$key] ?? $fallback);
}

function safe_next(mixed $value, string $fallback = '/profile'): string
{
    $path = is_string($value) ? trim($value) : '';
    return preg_match('#^/[a-zA-Z0-9/_?&=.%+-]*$#', $path) ? $path : $fallback;
}
