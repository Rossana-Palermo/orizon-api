<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    public string $method;
    public string $uri;
    private array $body;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $this->uri = rtrim($path, '/') ?: '/';

        $this->body = $this->parseBody();
    }

    private function parseBody(): array
    {
        $raw = file_get_contents('php://input');

        if (!$raw) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function input(string $key, $default = null)
    {
        return $this->body[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->body;
    }

    public function query(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
}
