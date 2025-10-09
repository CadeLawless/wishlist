<?php

namespace App\Core;

class View
{
    private string $viewsPath = 'views/';
    private array $shared = [];

    public function __construct()
    {
        $this->shared = [
            'app' => Config::get('app'),
            'user' => $this->getCurrentUser(),
            'flash' => $this->getFlashMessages()
        ];
    }

    public function render(string $view, array $data = []): string
    {
        $data = array_merge($this->shared, $data);
        
        // Extract variables for use in view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewFile = $this->viewsPath . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View file not found: {$viewFile}");
        }
        
        // Get the content and clean the buffer
        $content = ob_get_clean();
        
        return $content;
    }

    public function renderWithLayout(string $view, array $data = [], string $layout = 'main'): string
    {
        $data['content'] = $this->render($view, $data);
        return $this->render("layouts/{$layout}", $data);
    }

    public function share(string $key, $value): void
    {
        $this->shared[$key] = $value;
    }

    public function exists(string $view): bool
    {
        return file_exists($this->viewsPath . $view . '.php');
    }

    private function getCurrentUser(): ?array
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (isset($_SESSION['wishlist_logged_in']) && $_SESSION['wishlist_logged_in']) {
            return [
                'id' => $_SESSION['user_id'] ?? null,
                'username' => $_SESSION['username'] ?? null,
                'name' => $_SESSION['name'] ?? null,
                'email' => $_SESSION['user_email'] ?? null,
                'admin' => $_SESSION['admin'] ?? false,
                'dark' => $_SESSION['dark'] ?? false
            ];
        }
        
        return null;
    }

    private function getFlashMessages(): array
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }

    public function component(string $component, array $data = []): string
    {
        $data = array_merge($this->shared, $data);
        extract($data);
        
        ob_start();
        $componentFile = $this->viewsPath . "components/{$component}.php";
        if (file_exists($componentFile)) {
            include $componentFile;
        }
        return ob_get_clean();
    }
}
