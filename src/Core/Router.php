<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes[$path] = $handler;
    }

    public function resolve(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = filter_var($uri, FILTER_SANITIZE_URL);

        if (!array_key_exists($uri, $this->routes)) {
            return;
        }

        [$controller, $method] = $this->routes[$uri];
        $controller = new $controller();
        $result = $controller->$method();

        header('Content-Type: application/json');
        echo json_encode(['result' => $result]);
    }
}