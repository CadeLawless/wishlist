<?php

namespace App\Core;

/**
 * Base controller class providing common functionality for all controllers
 * 
 * Handles view rendering, redirects, authentication checks, and flash messages.
 * All application controllers should extend this class.
 */
abstract class Controller
{
    public function __construct(
        protected View $view = new View(),
        protected Request $request = new Request(),
        protected Response $response = new Response()
    ) {}

    protected function view(string $view, array $data = [], string $layout = 'main'): Response
    {
        $content = $this->view->renderWithLayout($view, $data, $layout);
        return Response::make($content);
    }

    /**
     * Add page-specific CSS to the layout's head section
     * 
     * Pass custom CSS through the data array using the 'customStyles' key.
     * The CSS will be automatically injected into the layout's <head> section
     * in a <style> block, maintaining clean MVC separation.
     * 
     * Example usage:
     * return $this->view('wishlist/show', [
     *     'wishlist' => $wishlist,
     *     'customStyles' => '
     *         .special-element { color: red; }
     *         .wishlist-specific { margin: 20px; }
     *     '
     * ]);
     * 
     * @param string $css The CSS rules to inject into the page
     * @return string The CSS wrapped in a style block
     */
    protected function addCustomStyles(string $css): string
    {
        return $css;
    }

    protected function json(array $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function redirect(string $url, int $status = 302): Response
    {
        return Response::redirect($url, $status);
    }

    protected function redirectBack(): Response
    {
        $referer = $this->request->server('HTTP_REFERER', '/');
        return $this->redirect($referer);
    }

    protected function redirectWithSuccess(string $url, string $message): Response
    {
        return $this->redirect($url)->withSuccess($message);
    }

    protected function redirectWithError(string $url, string $message): Response
    {
        return $this->redirect($url)->withError($message);
    }

    protected function redirectBackWithError(string $message): Response
    {
        return $this->redirectBack()->withError($message);
    }

    protected function redirectBackWithSuccess(string $message): Response
    {
        return $this->redirectBack()->withSuccess($message);
    }

    protected function validate(array $rules, array $data = null): array
    {
        $data = $data ?? $this->request->input();
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $fieldRules = is_string($rule) ? explode('|', $rule) : $rule;

            foreach ($fieldRules as $fieldRule) {
                $error = $this->validateField($field, $value, $fieldRule);
                if ($error) {
                    $errors[$field][] = $error;
                }
            }
        }

        return $errors;
    }

    private function validateField(string $field, $value, string $rule): ?string
    {
        if (strpos($rule, ':') !== false) {
            [$ruleName, $ruleValue] = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $ruleValue = null;
        }

        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    return ucfirst($field) . ' is required.';
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return ucfirst($field) . ' must be a valid email address.';
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < (int)$ruleValue) {
                    return ucfirst($field) . ' must be at least ' . $ruleValue . ' characters.';
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > (int)$ruleValue) {
                    return ucfirst($field) . ' must not exceed ' . $ruleValue . ' characters.';
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    return ucfirst($field) . ' must be numeric.';
                }
                break;

            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    return ucfirst($field) . ' must be a valid URL.';
                }
                break;
        }

        return null;
    }

    protected function auth(): ?array
    {
        return \App\Services\SessionManager::getAuthUser();
    }

    protected function requireAuth(): void
    {
        if (!$this->auth()) {
            $this->redirect('/wishlist/login')->send();
            exit;
        }
    }

    protected function requireGuest(): void
    {
        if ($this->auth()) {
            $this->redirect('/wishlist/')->send();
            exit;
        }
    }

    protected function requireAdmin(): void
    {
        $user = $this->auth();
        if (!$user || !$user['admin']) {
            $this->redirect('/wishlist/')->withError('Access denied. Admin privileges required.')->send();
            exit;
        }
    }
}
