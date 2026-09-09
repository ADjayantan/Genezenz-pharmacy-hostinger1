<?php

declare(strict_types=1);

namespace App\Core;

final class SecurityHeaders
{
    public static function send(bool $https): void
    {
        if (headers_sent()) {
            return;
        }

        $nonce = base64_encode(random_bytes(18));
        $GLOBALS['csp_nonce'] = $nonce;
        header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https:; style-src 'self'; script-src 'self' 'nonce-{$nonce}' https://www.googletagmanager.com; connect-src 'self' https://www.google-analytics.com https://region1.google-analytics.com; font-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'; object-src 'none'; upgrade-insecure-requests");
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Cross-Origin-Opener-Policy: same-origin');
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $indexing = Env::get('APP_ENV', 'production') === 'production'
            && filter_var(Env::get('SEO_INDEXING_ENABLED', 'false'), FILTER_VALIDATE_BOOL);
        if (!$indexing || preg_match('#^/(admin|api|login|register|logout|cart|checkout|profile|order|upload-prescription)(/|$)#', $path)) {
            header('X-Robots-Tag: noindex, nofollow');
        }
        // Session/CSRF/account pages must never be cached by a shared proxy.
        header('Cache-Control: private, no-store');

        if ($https && Env::get('APP_ENV', 'production') === 'production') {
            header('Strict-Transport-Security: max-age=31536000');
        }
    }
}
