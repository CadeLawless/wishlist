<?php

namespace App\Core;

abstract class Controller
{
    protected View $view;
    protected Request $request;
    protected Response $response;

    public function __construct()
    {
        $this->view = new View();
        $this->request = new Request();
        $this->response = new Response();
    }

    protected function view(string $view, array $data = [], string $layout = 'main'): Response
    {
        $content = $this->view->renderWithLayout($view, $data, $layout);
        return Response::make($content);
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
