<?php

namespace App\Validation;

abstract class BaseValidator
{
    /**
     * Validate required fields
     */
    protected function validateRequired(array $data, array $fields): array
    {
        $errors = [];
        
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[$field][] = ucfirst($field) . ' is required.';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate string length
     */
    protected function validateLength(array $data, string $field, ?int $min = null, ?int $max = null): array
    {
        $errors = [];
        
        if (!isset($data[$field]) || empty($data[$field])) {
            return $errors;
        }
        
        $length = strlen($data[$field]);
        
        if ($min !== null && $length < $min) {
            $errors[$field][] = ucfirst($field) . " must be at least {$min} characters.";
        }
        
        if ($max !== null && $length > $max) {
            $errors[$field][] = ucfirst($field) . " must not exceed {$max} characters.";
        }
        
        return $errors;
    }
    
    /**
     * Validate email format
     */
    protected function validateEmail(array $data, string $field = 'email'): array
    {
        $errors = [];
        
        if (!isset($data[$field]) || empty($data[$field])) {
            return $errors;
        }
        
        if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
            $errors[$field][] = 'Please enter a valid email address.';
        }
        
        return $errors;
    }
    
    /**
     * Validate password strength
     */
    protected function validatePassword(array $data, string $field = 'password'): array
    {
        $errors = [];
        
        if (!isset($data[$field]) || empty($data[$field])) {
            return $errors;
        }
        
        $password = $data[$field];
        
        if (strlen($password) < 8) {
            $errors[$field][] = 'Password must be at least 8 characters long.';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[$field][] = 'Password must contain at least one uppercase letter.';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[$field][] = 'Password must contain at least one lowercase letter.';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[$field][] = 'Password must contain at least one number.';
        }
        
        return $errors;
    }
    
    /**
     * Validate numeric values
     */
    protected function validateNumeric(array $data, string $field, ?float $min = null, ?float $max = null): array
    {
        $errors = [];
        
        if (!isset($data[$field]) || empty($data[$field])) {
            return $errors;
        }
        
        if (!is_numeric($data[$field])) {
            $errors[$field][] = ucfirst($field) . ' must be a valid number.';
            return $errors;
        }
        
        $value = (float) $data[$field];
        
        if ($min !== null && $value < $min) {
            $errors[$field][] = ucfirst($field) . " must be at least {$min}.";
        }
        
        if ($max !== null && $value > $max) {
            $errors[$field][] = ucfirst($field) . " must not exceed {$max}.";
        }
        
        return $errors;
    }

    /**
     * Validate characters not allowed
     */
    protected function validateCharactersNotAllowed(array $data, string $field, array $charactersNotAllowed): array
    {
        $errors = [];

        if (!isset($data[$field]) || empty($data[$field])) {
            return $errors;
        }

        foreach ($charactersNotAllowed as $char) {
            if (strpos($data[$field], $char) !== false) {
                $errors[$field][] = ucfirst($field) . " contains invalid characters: \"{$char}\".";
            }
        }

        return $errors;
    }
    
    /**
     * Merge validation errors
     */
    protected function mergeErrors(array ...$errorArrays): array
    {
        $merged = [];
        
        foreach ($errorArrays as $errors) {
            foreach ($errors as $field => $fieldErrors) {
                if (!isset($merged[$field])) {
                    $merged[$field] = [];
                }
                $merged[$field] = array_merge($merged[$field], $fieldErrors);
            }
        }
        
        return $merged;
    }
    
    /**
     * Check if there are any validation errors
     */
    public function hasErrors(array $errors): bool
    {
        return !empty($errors);
    }
    
    /**
     * Format errors for display
     */
    public function formatErrorsForDisplay(array $errors): string
    {
        if (empty($errors)) {
            return '';
        }

        $html = '<div class="submit-error"><strong>Please correct the following errors:</strong><ul>';
        
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $html .= '<li>' . htmlspecialchars($error) . '</li>';
            }
        }
        
        $html .= '</ul></div>';
        
        return $html;
    }
}
