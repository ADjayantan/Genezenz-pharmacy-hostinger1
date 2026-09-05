<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array{method: string, pattern: string, handler: callable}> */
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = ['method' => $method, 'pattern' => $pattern, 'handler' => $handler];
    }

    public function dispatch(Request $request): Response
    {
        $allowed = [];

        foreach ($this->routes as $route) {
            $regex = preg_replace_callback(
                '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
                static fn (array $match): string => '(?P<' . $match[1] . '>[^/]+)',
                $route['pattern']
            );
            $regex = '#^' . $regex . '$#';

            if (!preg_match($regex, $request->path, $matches)) {
                continue;
            }

            $allowed[] = $route['method'];
            if ($route['method'] !== $request->method) {
                continue;
            }

            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $request->params[$key] = (string) $value;
                }
            }

            return ($route['handler'])($request);
        }

        if ($allowed !== []) {
            return Response::json(['message' => 'Method not allowed.'], 405);
        }

        return Response::html(View::render('errors/404', [
            'title' => 'Page not found',
            'description' => 'The page you requested could not be found.',
        ]), 404);
    }
}
