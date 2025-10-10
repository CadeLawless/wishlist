<?php

namespace App\Services;

class ValidationService
{
    public function validateUser(array $data): array
    {
        $errors = [];

        // Username validation
        if (empty($data['username'])) {
            $errors['username'][] = 'Username is required.';
        } elseif (strlen($data['username']) < 3) {
            $errors['username'][] = 'Username must be at least 3 characters.';
        } elseif (strlen($data['username']) > 50) {
            $errors['username'][] = 'Username must not exceed 50 characters.';
        }

        // Name validation
        if (empty($data['name'])) {
            $errors['name'][] = 'Name is required.';
        } elseif (strlen($data['name']) < 2) {
            $errors['name'][] = 'Name must be at least 2 characters.';
        } elseif (strlen($data['name']) > 100) {
            $errors['name'][] = 'Name must not exceed 100 characters.';
        }

        // Email validation
        if (empty($data['email'])) {
            $errors['email'][] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Email must be a valid email address.';
        }

        // Password validation
        if (empty($data['password'])) {
            $errors['password'][] = 'Password is required.';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'][] = 'Password must be at least 6 characters.';
        }

        return $errors;
    }

    public function validateWishlist(array $data): array
    {
        $errors = [];

        // Type validation
        if (empty($data['type'])) {
            $errors['type'][] = 'Wishlist type is required.';
        } elseif (!in_array($data['type'], ['Birthday', 'Christmas'])) {
            $errors['type'][] = 'Please select a valid wishlist type.';
        }

        // Name validation
        if (empty($data['wishlist_name'])) {
            $errors['wishlist_name'][] = 'Wishlist name is required.';
        } elseif (strlen($data['wishlist_name']) < 2) {
            $errors['wishlist_name'][] = 'Wishlist name must be at least 2 characters.';
        } elseif (strlen($data['wishlist_name']) > 100) {
            $errors['wishlist_name'][] = 'Wishlist name must not exceed 100 characters.';
        }

        return $errors;
    }

    public function validateItem(array $data): array
    {
        $errors = [];

        // Name validation
        if (empty($data['name'])) {
            $errors['name'][] = 'Item name is required.';
        } elseif (strlen($data['name']) < 2) {
            $errors['name'][] = 'Item name must be at least 2 characters.';
        } elseif (strlen($data['name']) > 200) {
            $errors['name'][] = 'Item name must not exceed 200 characters.';
        }

        // Price validation
        if (!empty($data['price'])) {
            if (!is_numeric($data['price']) || $data['price'] < 0) {
                $errors['price'][] = 'Price must be a valid positive number.';
            }
        }

        // Quantity validation
        if (empty($data['quantity'])) {
            $errors['quantity'][] = 'Quantity is required.';
        } elseif (!is_numeric($data['quantity']) || $data['quantity'] < 1) {
            $errors['quantity'][] = 'Quantity must be a positive number.';
        }

        // Link validation
        if (!empty($data['link'])) {
            if (!filter_var($data['link'], FILTER_VALIDATE_URL)) {
                $errors['link'][] = 'Link must be a valid URL.';
            }
        }

        // Priority validation
        if (!empty($data['priority'])) {
            if (!in_array($data['priority'], ['1', '2', '3', '4'])) {
                $errors['priority'][] = 'Please select a valid priority.';
            }
        }

        return $errors;
    }

    public function validateLogin(array $data): array
    {
        $errors = [];

        if (empty($data['username'])) {
            $errors['username'][] = 'Username or email is required.';
        }

        if (empty($data['password'])) {
            $errors['password'][] = 'Password is required.';
        }

        return $errors;
    }

    public function validatePasswordReset(array $data): array
    {
        $errors = [];

        if (empty($data['email'])) {
            $errors['email'][] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Email must be a valid email address.';
        }

        return $errors;
    }

    public function validateNewPassword(array $data): array
    {
        $errors = [];

        if (empty($data['password'])) {
            $errors['password'][] = 'Password is required.';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'][] = 'Password must be at least 6 characters.';
        }

        if (empty($data['password_confirmation'])) {
            $errors['password_confirmation'][] = 'Password confirmation is required.';
        } elseif ($data['password'] !== $data['password_confirmation']) {
            $errors['password_confirmation'][] = 'Passwords do not match.';
        }

        return $errors;
    }

    public function validateWishlistName(string $name): array
    {
        $errors = [];

        if (empty($name)) {
            $errors['wishlist_name'][] = 'Wishlist name is required.';
        } elseif (strlen($name) < 2) {
            $errors['wishlist_name'][] = 'Wishlist name must be at least 2 characters.';
        } elseif (strlen($name) > 100) {
            $errors['wishlist_name'][] = 'Wishlist name must not exceed 100 characters.';
        }

        return $errors;
    }

    public function hasErrors(array $errors): bool
    {
        return !empty(array_filter($errors));
    }

    public function getErrorMessages(array $errors): array
    {
        $messages = [];
        foreach ($errors as $field => $fieldErrors) {
            $messages = array_merge($messages, $fieldErrors);
        }
        return $messages;
    }

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
