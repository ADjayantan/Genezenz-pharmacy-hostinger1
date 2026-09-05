<?php

declare(strict_types=1);

use App\Core\Env;
use App\Core\SecurityHeaders;

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'public_html');

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
    $path = BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $relative . '.php';
    if (is_file($path)) {
        require $path;
    }
});

Env::load(BASE_PATH . DIRECTORY_SEPARATOR . '.env');
date_default_timezone_set('UTC');

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

session_name(Env::get('SESSION_NAME', 'genezenz_session'));
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

SecurityHeaders::send($isHttps);

require BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Support' . DIRECTORY_SEPARATOR . 'helpers.php';

set_exception_handler(static function (Throwable $error): void {
    error_log(sprintf(
        '[%s] %s in %s:%d',
        gmdate('c'),
        $error->getMessage(),
        $error->getFile(),
        $error->getLine()
    ));

    $debug = filter_var(Env::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL);
    http_response_code(500);

    if (!headers_sent()) {
        header('Content-Type: text/html; charset=UTF-8');
    }

    $message = $debug ? $error->getMessage() : 'The pharmacy website is temporarily unavailable.';
    $view = BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . '500.php';
    if (is_file($view)) {
        require $view;
        return;
    }

    echo '<h1>Something went wrong</h1><p>' . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
});
