<?php

namespace App\Core;

class Response
{
    private string $content;
    private int $status;
    private array $headers = [];

    public function __construct(string $content = '', int $status = 200, array $headers = [])
    {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    public static function make(string $content = '', int $status = 200, array $headers = []): self
    {
        return new self($content, $status, $headers);
    }

    public static function json(array $data, int $status = 200, array $headers = []): self
    {
        $headers['Content-Type'] = 'application/json';
        return new self(json_encode($data), $status, $headers);
    }

    public static function redirect(string $url, int $status = 302): self
    {
        return new self('', $status, ['Location' => $url]);
    }

    public static function view(string $view, array $data = [], int $status = 200): self
    {
        $viewRenderer = new View();
        $content = $viewRenderer->render($view, $data);
        return new self($content, $status);
    }

    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function withFlash(string $type, string $message): self
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'][$type] = $message;
        return $this;
    }

    public function withSuccess(string $message): self
    {
        return $this->withFlash('success', $message);
    }

    public function withError(string $message): self
    {
        return $this->withFlash('error', $message);
    }

    public function withWarning(string $message): self
    {
        return $this->withFlash('warning', $message);
    }

    public function withInfo(string $message): self
    {
        return $this->withFlash('info', $message);
    }

    public function send(): void
    {
        // Set status code
        http_response_code($this->status);

        // Set headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // For redirects, exit after sending headers to prevent further execution
        if ($this->status >= 300 && $this->status < 400) {
            exit();
        }

        // Output content
        echo $this->content;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
