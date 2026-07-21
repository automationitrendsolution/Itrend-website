<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Tiny path-based router. Routes map a method + path to "Controller@action".
 */
final class Router
{
    /** @var array<string, array<string, string>> */
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][$this->normalise($path)] = $handler;
    }

    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][$this->normalise($path)] = $handler;
    }

    private function normalise(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '' ? '/' : $path;
    }

    public function dispatch(): void
    {
        $request = new Request();
        $method = $request->method() === 'HEAD' ? 'GET' : $request->method();
        $path = $request->path();

        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null) {
            $this->notFound($request);
            return;
        }

        [$controllerName, $action] = explode('@', $handler);
        $class = 'App\\Controllers\\' . $controllerName;

        if (!class_exists($class) || !method_exists($class, $action)) {
            $this->serverError($request, "Handler $handler not found");
            return;
        }

        try {
            $controller = new $class();
            $controller->$action($request);
        } catch (\Throwable $e) {
            error_log('[router] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            $this->serverError($request, $e->getMessage());
        }
    }

    private function notFound(Request $request): void
    {
        http_response_code(404);
        if ($request->expectsJson()) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'Not found']);
            return;
        }
        $controller = new \App\Controllers\PageController();
        $controller->notFound($request);
    }

    private function serverError(Request $request, string $message): void
    {
        http_response_code(500);
        $debug = Config::bool('APP_DEBUG', false);
        if ($request->expectsJson()) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => $debug ? $message : 'Server error']);
            return;
        }
        $controller = new \App\Controllers\PageController();
        $controller->serverError($request, $debug ? $message : null);
    }
}
