<?php

namespace App\Core;

class Request
{
    private array $params = [];
    private string $method;
    private string $path;
    private array $query;
    private array $post;
    private array $files;
    private array $server;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Handle method spoofing for DELETE, PUT, PATCH requests
        if ($this->method === 'POST' && isset($_POST['_method'])) {
            $this->method = strtoupper($_POST['_method']);
        }
        
        $fullPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        
        // Remove the base path /wishlist/ from the path
        $basePath = '/wishlist';
        if (strpos($fullPath, $basePath) === 0) {
            $this->path = substr($fullPath, strlen($basePath)) ?: '/';
        } else {
            $this->path = $fullPath;
        }
        
        $this->query = $_GET ?? [];
        $this->post = $_POST ?? [];
        $this->files = $_FILES ?? [];
        $this->server = $_SERVER ?? [];
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($this->method) === strtoupper($method);
    }

    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    public function isPut(): bool
    {
        return $this->isMethod('PUT');
    }

    public function isDelete(): bool
    {
        return $this->isMethod('DELETE');
    }

    public function get(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    public function post(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->post;
        }
        return $this->post[$key] ?? $default;
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return array_merge($this->query, $this->post);
        }
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    public function file(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->files;
        }
        return $this->files[$key] ?? null;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function server(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->server;
        }
        return $this->server[$key] ?? $default;
    }

    public function header(string $name, mixed $default = null): mixed
    {
        $name = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server($name, $default);
    }

    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public function url(): string
    {
        $scheme = $this->server('HTTPS') ? 'https' : 'http';
        $host = $this->server('HTTP_HOST');
        $path = $this->path();
        return "{$scheme}://{$host}{$path}";
    }

    public function fullUrl(): string
    {
        $scheme = $this->server('HTTPS') ? 'https' : 'http';
        $host = $this->server('HTTP_HOST');
        $uri = $this->server('REQUEST_URI');
        return "{$scheme}://{$host}{$uri}";
    }

    public function ip(): string
    {
        return $this->server('HTTP_X_FORWARDED_FOR') 
            ?? $this->server('HTTP_X_REAL_IP') 
            ?? $this->server('REMOTE_ADDR') 
            ?? 'unknown';
    }

    public function userAgent(): string
    {
        return $this->server('HTTP_USER_AGENT', '');
    }
}
