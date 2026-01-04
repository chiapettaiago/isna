<?php

declare(strict_types=1);

class Router
{
    private array $routes = [];

    public function add(string $path, array $meta): void
    {
        $this->routes[$path] = $meta;
    }

    public function dispatch(string $path): array
    {
        if (array_key_exists($path, $this->routes)) {
            return $this->routes[$path];
        }

        return ['file' => null, 'title' => 'Página Não Encontrada - ISNA', 'status' => 404];
    }
}
