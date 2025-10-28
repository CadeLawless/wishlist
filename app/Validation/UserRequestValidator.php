<?php

namespace App\Validation;

use App\Core\Constants;

use App\Models\User;

class UserRequestValidator extends BaseValidator
{
    /**
     * Validate user registration data
     */
    public function validateRegistration(array $data): array
    {
        $errors = [];
        
        // Required fields
        $requiredErrors = $this->validateRequired($data, ['username', 'name', 'email', 'password']);
        $errors = $this->mergeErrors($errors, $requiredErrors);
        
        // Username validation
        $usernameErrors = $this->validateLength($data, 'username', Constants::MIN_USERNAME_LENGTH, Constants::MAX_USERNAME_LENGTH);
        $errors = $this->mergeErrors($errors, $usernameErrors);
        
        // Name validation
        $nameErrors = $this->validateLength($data, 'name', Constants::MIN_NAME_LENGTH, Constants::MAX_NAME_LENGTH);
        $errors = $this->mergeErrors($errors, $nameErrors);
        
        // Email validation
        $emailErrors = $this->validateEmail($data);
        $errors = $this->mergeErrors($errors, $emailErrors);
        
        // Password validation
        $passwordErrors = $this->validatePassword($data);
        $errors = $this->mergeErrors($errors, $passwordErrors);
        
        // Check if username already exists
        if (!empty($data['username'])) {
            $existingUser = User::findByUsernameOrEmail($data['username']);
            if ($existingUser) {
                $errors['username'][] = 'Username already exists.';
            }
        }
        
        // Check if email already exists
        if (!empty($data['email'])) {
            $existingUser = User::findByUsernameOrEmail($data['email']);
            if ($existingUser) {
                $errors['email'][] = 'Email already exists.';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate login data
     */
    public function validateLogin(array $data): array
    {
        $errors = [];
        
        // Required fields
        $requiredErrors = $this->validateRequired($data, ['username', 'password']);
        $errors = $this->mergeErrors($errors, $requiredErrors);
        
        return $errors;
    }
    
    /**
     * Validate name update
     */
    public function validateNameUpdate(array $data): array
    {
        $errors = [];
        
        // Required field
        $requiredErrors = $this->validateRequired($data, ['name']);
        $errors = $this->mergeErrors($errors, $requiredErrors);
        
        // Name validation
        $nameErrors = $this->validateLength($data, 'name', Constants::MIN_NAME_LENGTH, Constants::MAX_NAME_LENGTH);
        $errors = $this->mergeErrors($errors, $nameErrors);
        
        return $errors;
    }
    
    /**
     * Validate email update
     */
    public function validateEmailUpdate(array $data): array
    {
        $errors = [];
        
        // Required field
        $requiredErrors = $this->validateRequired($data, ['email']);
        $errors = $this->mergeErrors($errors, $requiredErrors);
        
        // Email validation
        $emailErrors = $this->validateEmail($data);
        $errors = $this->mergeErrors($errors, $emailErrors);
        
        // Check if email already exists
        if (!empty($data['email'])) {
            $existingUser = User::findByUsernameOrEmail($data['email']);
            if ($existingUser) {
                $errors['email'][] = 'Email already exists.';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate password change
     */
    public function validatePasswordChange(array $data): array
    {
        $errors = [];
        
        // Required fields
        $requiredErrors = $this->validateRequired($data, ['current_password', 'new_password', 'confirm_password']);
        $errors = $this->mergeErrors($errors, $requiredErrors);
        
        // New password validation
        $passwordErrors = $this->validatePassword($data, 'new_password');
        $errors = $this->mergeErrors($errors, $passwordErrors);
        
        // Confirm password match
        if (!empty($data['new_password']) && !empty($data['confirm_password'])) {
            if ($data['new_password'] !== $data['confirm_password']) {
                $errors['confirm_password'][] = 'Passwords do not match.';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate password reset request
     */
    public function validatePasswordReset(array $data): array
    {
        $errors = [];
        
        // Required field
        $requiredErrors = $this->validateRequired($data, ['email']);
        $errors = $this->mergeErrors($errors, $requiredErrors);
        
        // Email validation
        $emailErrors = $this->validateEmail($data);
        $errors = $this->mergeErrors($errors, $emailErrors);
        
        return $errors;
    }
    
    /**
     * Validate new password
     */
    public function validateNewPassword(array $data): array
    {
        $errors = [];
        
        // Required fields
        $requiredErrors = $this->validateRequired($data, ['password', 'confirm_password']);
        $errors = $this->mergeErrors($errors, $requiredErrors);
        
        // Password validation
        $passwordErrors = $this->validatePassword($data);
        $errors = $this->mergeErrors($errors, $passwordErrors);
        
        // Confirm password match
        if (!empty($data['password']) && !empty($data['confirm_password'])) {
            if ($data['password'] !== $data['confirm_password']) {
                $errors['confirm_password'][] = 'Passwords do not match.';
            }
        }
        
        return $errors;
    }
}
