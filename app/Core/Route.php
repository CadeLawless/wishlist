<?php

namespace App\Core;

class Route
{
    private string $method;
    private string $path;
    private $handler;
    private array $middleware = [];
    private array $params = [];

    public function __construct(string $method, string $path, $handler)
    {
        $this->method = $method;
        $this->path = $path;
        $this->handler = $handler;
    }

    public function middleware(string $name): self
    {
        $this->middleware[] = $name;
        return $this;
    }

    public function matches(string $method, string $path): bool
    {
        if ($this->method !== $method) {
            return false;
        }

        // Convert route path to regex pattern
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $this->path);
        $pattern = '#^' . $pattern . '$#';

        return preg_match($pattern, $path);
    }

    public function extractParams(string $path): array
    {
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $this->path);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $path, $matches)) {
            array_shift($matches); // Remove full match
            
            // Extract parameter names from route
            preg_match_all('/\{([^}]+)\}/', $this->path, $paramNames);
            $paramNames = $paramNames[1];
            
            $params = [];
            foreach ($paramNames as $index => $name) {
                $params[$name] = $matches[$index] ?? null;
            }
            
            return $params;
        }

        return [];
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
