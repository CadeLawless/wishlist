<?php

namespace App\Validation;

class ItemRequestValidator extends BaseValidator
{
    /**
     * Validate item creation/update data
     * 
     * @param array $data The form data to validate
     * @param bool $isEdit Whether this is an edit operation (has existing image)
     * @param string|null $existingImage The existing image filename (for edit operations)
     */
    public function validateItem(array $data, bool $isEdit = false, ?string $existingImage = null): array
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
        
        // Image validation
        $hasImage = false;
        
        // Check for file upload (marked as 'uploaded' in validation data)
        if (isset($data['item_image']) && ($data['item_image'] === 'uploaded' || !empty($data['item_image']))) {
            $hasImage = true;
        }
        
        // Check for paste image (base64 or URL)
        if (!empty($data['paste_image'])) {
            $hasImage = true;
        }
        
        // Check for temp filename (from previous validation error)
        if (!empty($data['temp_filename'])) {
            $hasImage = true;
        }
        
        // For edit: check if existing image is being kept
        if ($isEdit && !empty($existingImage)) {
            // If no new image provided, existing image is sufficient
            if (!$hasImage) {
                $hasImage = true; // Existing image counts
            }
        }
        
        // Image is required
        if (!$hasImage) {
            $errors['item_image'][] = 'Item image is required.';
        }
        
        return $errors;
    }
}
