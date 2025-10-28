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


    protected function json(array $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function redirect(string $url, int $status = 302): Response
    {
        return Response::redirect($url, $status);
    }



    protected function validate(array $rules, ?array $data = null): array
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

        return match ($ruleName) {
            'required' => empty($value) ? ucfirst($field) . ' is required.' : null,
            'email' => (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) ? ucfirst($field) . ' must be a valid email address.' : null,
            'min' => (!empty($value) && strlen($value) < (int)$ruleValue) ? ucfirst($field) . ' must be at least ' . $ruleValue . ' characters.' : null,
            'max' => (!empty($value) && strlen($value) > (int)$ruleValue) ? ucfirst($field) . ' must not exceed ' . $ruleValue . ' characters.' : null,
            'numeric' => (!empty($value) && !is_numeric($value)) ? ucfirst($field) . ' must be numeric.' : null,
            'url' => (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) ? ucfirst($field) . ' must be a valid URL.' : null,
            default => null
        };
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
