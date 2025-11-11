<?php

namespace App\Core;

class View
{
    private string $viewsPath;
    private array $shared = [];

    public function __construct()
    {
        $this->viewsPath = __DIR__ . '/../../views/';
        $this->shared = [
            'app' => \App\Core\Config::get('app'),
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
        return \App\Services\SessionManager::getAuthUser();
    }

    private function getFlashMessages(): array
    {
        return \App\Services\SessionManager::getFlashMessages();
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
