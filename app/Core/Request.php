<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    /** @param array<string, string> $params */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $form,
        public readonly array $headers,
        public array $params = []
    ) {
    }

    public static function capture(): self
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $path = '/' . trim(rawurldecode($uri), '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        $headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];
        $normalized = [];
        foreach ($headers as $key => $value) {
            $normalized[strtolower((string) $key)] = (string) $value;
        }

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            $path,
            $_GET,
            $_POST,
            $normalized
        );
    }

    /** @return array<string, mixed> */
    public function json(): array
    {
        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /** @return array<string, mixed> */
    public function input(): array
    {
        return str_contains($this->headers['content-type'] ?? '', 'application/json')
            ? $this->json()
            : $this->form;
    }

    public function expectsJson(): bool
    {
        return str_contains($this->headers['accept'] ?? '', 'application/json')
            || str_contains($this->headers['content-type'] ?? '', 'application/json');
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
