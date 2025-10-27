<?php

namespace App\Validation;

class WishlistRequestValidator extends BaseValidator
{
    /**
     * Validate wishlist creation/update data
     */
    public function validateWishlist(array $data): array
    {
        $errors = [];
        
        // Required fields
        $requiredErrors = $this->validateRequired($data, ['wishlist_name', 'wishlist_type']);
        $errors = $this->mergeErrors($errors, $requiredErrors);
        
        // Wishlist name validation
        $nameErrors = $this->validateLength($data, 'wishlist_name', 1, 100);
        $errors = $this->mergeErrors($errors, $nameErrors);
        
        // Wishlist type validation
        if (!empty($data['wishlist_type'])) {
            $validTypes = ['Birthday', 'Christmas', 'General'];
            if (!in_array($data['wishlist_type'], $validTypes)) {
                $errors['wishlist_type'][] = 'Please select a valid wishlist type.';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate wishlist name update
     */
    public function validateWishlistName(string $name): array
    {
        $errors = [];
        
        if (empty($name)) {
            $errors['wishlist_name'][] = 'Wishlist name is required.';
        } elseif (strlen($name) < 1) {
            $errors['wishlist_name'][] = 'Wishlist name must be at least 1 character.';
        } elseif (strlen($name) > 100) {
            $errors['wishlist_name'][] = 'Wishlist name must not exceed 100 characters.';
        }
        
        return $errors;
    }
}
