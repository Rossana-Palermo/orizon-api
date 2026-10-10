<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
    {
        // Converte i placeholder {id} in un gruppo di cattura regex,
        // cosi' da poter estrarre dinamicamente i parametri dall'URI.
        $regex = preg_replace('#\{[a-zA-Z]+\}#', '([a-zA-Z0-9_-]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        $this->routes[] = [
            'method' => strtoupper($method),
            'regex' => $regex,
            'handler' => $handler,
        ];
    }

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function put(string $pattern, callable $handler): void
    {
        $this->add('PUT', $pattern, $handler);
    }

    public function delete(string $pattern, callable $handler): void
    {
        $this->add('DELETE', $pattern, $handler);
    }

    public function dispatch(Request $request): void
    {
        $pathMatched = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $request->uri, $matches)) {
                continue;
            }

            $pathMatched = true;

            if ($route['method'] !== $request->method) {
                continue;
            }

            array_shift($matches); // rimuove il match completo, lascia solo i parametri
            call_user_func_array($route['handler'], array_merge([$request], $matches));
            return;
        }

        if ($pathMatched) {
            Response::json(['error' => 'Metodo non consentito per questa risorsa'], 405);
        } else {
            Response::json(['error' => 'Risorsa non trovata'], 404);
        }
    }
}
