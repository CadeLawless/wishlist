<?php

namespace App\Validation;

class ItemRequestValidator extends BaseValidator
{
    /**
     * Validate item creation/update data
     */
    public function validateItem(array $data): array
    {
        $errors = [];
        
        // Required fields
        $requiredErrors = $this->validateRequired($data, ['name', 'price']);
        $errors = $this->mergeErrors($errors, $requiredErrors);
        
        // Item name validation
        $nameErrors = $this->validateLength($data, 'name', 1, 100);
        $errors = $this->mergeErrors($errors, $nameErrors);
        
        // Price validation
        $priceErrors = $this->validateNumeric($data, 'price', 0);
        $errors = $this->mergeErrors($errors, $priceErrors);
        
        // Quantity validation (if not unlimited)
        if (!empty($data['quantity']) && ($data['unlimited'] ?? 'No') !== 'Yes') {
            $quantityErrors = $this->validateNumeric($data, 'quantity', 1);
            $errors = $this->mergeErrors($errors, $quantityErrors);
        }
        
        // Priority validation
        if (!empty($data['priority'])) {
            $priority = (int) $data['priority'];
            if ($priority < 1 || $priority > 4) {
                $errors['priority'][] = 'Priority must be between 1 and 4.';
            }
        }
        
        // Link validation (if provided)
        if (!empty($data['link'])) {
            if (!filter_var($data['link'], FILTER_VALIDATE_URL)) {
                $errors['link'][] = 'Please enter a valid URL.';
            }
        }
        
        return $errors;
    }
}
