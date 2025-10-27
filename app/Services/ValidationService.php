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
        if (empty($data['wishlist_type'])) {
            $errors['wishlist_type'][] = 'Wishlist type is required.';
        } elseif (!in_array($data['wishlist_type'], ['Birthday', 'Christmas'])) {
            $errors['wishlist_type'][] = 'Please select a valid wishlist type.';
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

        // Price validation - US currency format
        if (empty($data['price'])) {
            $errors['price'][] = 'Item price is required.';
        } else {
            // Remove dollar sign and commas for validation
            $price = str_replace(['$', ','], '', $data['price']);
            if (!is_numeric($price) || $price < 0) {
                $errors['price'][] = 'Item price must be a valid positive number.';
            } elseif ($price > 999999.99) {
                $errors['price'][] = 'Item price must not exceed $999,999.99.';
            }
        }

        // Quantity validation - handle unlimited option
        $unlimited = isset($data['unlimited']) && $data['unlimited'] == 'Yes';
        if (!$unlimited) {
            if (empty($data['quantity'])) {
                $errors['quantity'][] = 'Quantity is required when not unlimited.';
            } elseif (!is_numeric($data['quantity']) || $data['quantity'] < 1) {
                $errors['quantity'][] = 'Quantity must be a positive number.';
            } elseif ($data['quantity'] > 999) {
                $errors['quantity'][] = 'Quantity must not exceed 999.';
            }
        }

        // Link validation - must be valid URL
        if (empty($data['link'])) {
            $errors['link'][] = 'Item URL is required.';
        } else {
            if (!filter_var($data['link'], FILTER_VALIDATE_URL)) {
                $errors['link'][] = 'Please enter a valid URL for Item URL.';
            } elseif (strlen($data['link']) > 500) {
                $errors['link'][] = 'Item URL must not exceed 500 characters.';
            }
        }

        // Priority validation - required field
        if (empty($data['priority'])) {
            $errors['priority'][] = 'How much do you want this item is required.';
        } elseif (!in_array($data['priority'], ['1', '2', '3', '4'])) {
            $errors['priority'][] = 'Please select a valid priority.';
        }

        // Notes validation (optional but length check)
        if (!empty($data['notes']) && strlen($data['notes']) > 1000) {
            $errors['notes'][] = 'Item notes must not exceed 1000 characters.';
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

    public function validateName(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'][] = 'Name is required.';
        } elseif (strlen($data['name']) < 2) {
            $errors['name'][] = 'Name must be at least 2 characters.';
        } elseif (strlen($data['name']) > 100) {
            $errors['name'][] = 'Name must not exceed 100 characters.';
        }

        return $errors;
    }

    public function validateEmail(array $data): array
    {
        $errors = [];

        if (empty($data['email'])) {
            $errors['email'][] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Email must be a valid email address.';
        }

        return $errors;
    }

    public function validatePasswordChange(array $data): array
    {
        $errors = [];

        if (empty($data['current_password'])) {
            $errors['current_password'][] = 'Current password is required.';
        }

        if (empty($data['new_password'])) {
            $errors['new_password'][] = 'New password is required.';
        } elseif (strlen($data['new_password']) < 6) {
            $errors['new_password'][] = 'New password must be at least 6 characters.';
        } elseif (!preg_match('/^(?=.*[0-9])(?=.*[a-zA-Z])(.+){6,}$/', $data['new_password'])) {
            $errors['new_password'][] = 'New password must include at least one letter and one number.';
        }

        if (empty($data['confirm_password'])) {
            $errors['confirm_password'][] = 'Confirm password is required.';
        } elseif ($data['new_password'] !== $data['confirm_password']) {
            $errors['confirm_password'][] = 'New password and confirm password must match.';
        }

        return $errors;
    }
}
