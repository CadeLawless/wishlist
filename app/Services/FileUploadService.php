<?php

namespace App\Services;

use App\Core\Database;

class FileUploadService
{
    private array $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    private int $maxFileSize = 5 * 1024 * 1024; // 5MB

    public function uploadItemImage(array $file, string $wishlistId, string $itemName): array
    {
        $result = [
            'success' => false,
            'filename' => null,
            'filepath' => null,
            'error' => null
        ];

        // Validate file
        if (!$this->validateFile($file)) {
            $result['error'] = 'Invalid file. Please upload a valid image file (JPG, PNG, WEBP) under 5MB.';
            return $result;
        }

        // Create directory if it doesn't exist
        $uploadDir = "images/item-images/{$wishlistId}/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $this->sanitizeFilename($itemName) . '.' . $extension;

        // Handle filename conflicts
        $filename = $this->getUniqueFilename($uploadDir, $filename);

        // Move uploaded file
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Optimize image if needed
            $this->optimizeImage($targetPath);
            
            $result['success'] = true;
            $result['filename'] = $filename;
            $result['filepath'] = $targetPath;
        } else {
            $result['error'] = 'Failed to upload file. Please try again.';
        }

        return $result;
    }

    public function copyItemImage(string $sourcePath, string $targetWishlistId, string $filename): array
    {
        $result = [
            'success' => false,
            'filename' => null,
            'error' => null
        ];

        // Create target directory if it doesn't exist
        $targetDir = "images/item-images/{$targetWishlistId}/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Handle filename conflicts
        $targetFilename = $this->getUniqueFilename($targetDir, $filename);
        $targetPath = $targetDir . $targetFilename;

        if (copy($sourcePath, $targetPath)) {
            $result['success'] = true;
            $result['filename'] = $targetFilename;
        } else {
            $result['error'] = 'Failed to copy image file.';
        }

        return $result;
    }

    public function deleteItemImage(string $wishlistId, string $filename): bool
    {
        $filePath = "images/item-images/{$wishlistId}/{$filename}";
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    private function validateFile(array $file): bool
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            return false;
        }

        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            return false;
        }

        // Additional MIME type check (if Fileinfo extension is available)
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedMimeTypes = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            return in_array($mimeType, $allowedMimeTypes);
        }

        // If Fileinfo is not available, rely on file extension check only
        return true;
    }

    private function sanitizeFilename(string $filename): string
    {
        // Remove special characters and limit length
        $filename = preg_replace('/[^a-zA-Z0-9\-\s]/', '', $filename);
        $filename = substr($filename, 0, 200);
        return trim($filename);
    }

    private function getUniqueFilename(string $directory, string $filename): string
    {
        $pathInfo = pathinfo($filename);
        $name = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        $counter = 1;

        while (file_exists($directory . $filename)) {
            $filename = $name . $counter . '.' . $extension;
            $counter++;
        }

        return $filename;
    }

    public function uploadFromBase64(string $base64Data, string $wishlistId, string $itemName): array
    {
        $result = [
            'success' => false,
            'filename' => null,
            'filepath' => null,
            'error' => null
        ];

        // Validate base64 data
        if (!$this->validateBase64Image($base64Data)) {
            $result['error'] = 'Invalid image data. Please paste a valid image.';
            return $result;
        }

        // Create directory if it doesn't exist
        $uploadDir = "images/item-images/{$wishlistId}/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Decode base64 data
        $imageData = base64_decode($base64Data);
        if ($imageData === false) {
            $result['error'] = 'Failed to decode image data.';
            return $result;
        }

        // Detect image type and extension
        $imageInfo = getimagesizefromstring($imageData);
        if ($imageInfo === false) {
            $result['error'] = 'Invalid image format.';
            return $result;
        }

        $mimeType = $imageInfo['mime'];
        $extension = $this->getExtensionFromMimeType($mimeType);
        
        if (!$extension) {
            $result['error'] = 'Unsupported image format. Please use JPG, PNG, or WEBP.';
            return $result;
        }

        // Generate filename
        $filename = $this->sanitizeFilename($itemName) . '.' . $extension;
        $filename = $this->getUniqueFilename($uploadDir, $filename);

        // Save image
        $targetPath = $uploadDir . $filename;
        if (file_put_contents($targetPath, $imageData)) {
            // Optimize image if needed
            $this->optimizeImage($targetPath);
            
            $result['success'] = true;
            $result['filename'] = $filename;
            $result['filepath'] = $targetPath;
        } else {
            $result['error'] = 'Failed to save image. Please try again.';
        }

        return $result;
    }

    public function updateCopiedItemImages(string $copyId, string $oldImage, string $newImage): bool
    {
        try {
            // Find all items with this copy_id
            $stmt = Database::query(
                "SELECT wishlist_id, image FROM items WHERE copy_id = ?",
                [$copyId]
            );
            $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            foreach ($items as $item) {
                $wishlistId = $item['wishlist_id'];
                $currentImage = $item['image'];
                
                // Skip if this is the same image
                if ($currentImage === $newImage) {
                    continue;
                }

                // Delete old image if it exists and is different
                if ($currentImage !== $oldImage) {
                    $this->deleteItemImage($wishlistId, $currentImage);
                }

                // Copy new image to this wishlist
                $sourcePath = "images/item-images/{$wishlistId}/{$newImage}";
                $targetDir = "images/item-images/{$wishlistId}/";
                
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                $targetFilename = $this->getUniqueFilename($targetDir, $newImage);
                $targetPath = $targetDir . $targetFilename;

                if (copy($sourcePath, $targetPath)) {
                    // Update database with new filename
                    Database::query(
                        "UPDATE items SET image = ? WHERE copy_id = ? AND wishlist_id = ?",
                        [$targetFilename, $copyId, $wishlistId]
                    );
                }
            }

            return true;
        } catch (\Exception $e) {
            error_log('Failed to update copied item images: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteImageFromAllWishlists(string $copyId, string $imageName): bool
    {
        try {
            // Find all items with this copy_id
            $stmt = Database::query(
                "SELECT wishlist_id FROM items WHERE copy_id = ?",
                [$copyId]
            );
            $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            foreach ($items as $item) {
                $this->deleteItemImage($item['wishlist_id'], $imageName);
            }

            return true;
        } catch (\Exception $e) {
            error_log('Failed to delete images from all wishlists: ' . $e->getMessage());
            return false;
        }
    }

    public function optimizeImage(string $filePath): bool
    {
        try {
            $imageInfo = getimagesize($filePath);
            if (!$imageInfo) {
                return false;
            }

            $mimeType = $imageInfo['mime'];
            $width = $imageInfo[0];
            $height = $imageInfo[1];

            // Only optimize if image is larger than 1920x1080
            if ($width <= 1920 && $height <= 1080) {
                return true;
            }

            // Calculate new dimensions (maintain aspect ratio)
            $maxWidth = 1920;
            $maxHeight = 1080;
            
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);

            // Create image resource based on type
            switch ($mimeType) {
                case 'image/jpeg':
                    $source = imagecreatefromjpeg($filePath);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($filePath);
                    break;
                case 'image/webp':
                    $source = imagecreatefromwebp($filePath);
                    break;
                default:
                    return false;
            }

            if (!$source) {
                return false;
            }

            // Create resized image
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG
            if ($mimeType === 'image/png') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }

            imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Save optimized image
            $success = false;
            switch ($mimeType) {
                case 'image/jpeg':
                    $success = imagejpeg($resized, $filePath, 85); // 85% quality
                    break;
                case 'image/png':
                    $success = imagepng($resized, $filePath, 8); // 8 compression level
                    break;
                case 'image/webp':
                    $success = imagewebp($resized, $filePath, 85); // 85% quality
                    break;
            }

            // Clean up
            imagedestroy($source);
            imagedestroy($resized);

            return $success;
        } catch (\Exception $e) {
            error_log('Image optimization failed: ' . $e->getMessage());
            return false;
        }
    }

    private function validateBase64Image(string $base64Data): bool
    {
        // Check if it's valid base64
        if (!preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,/', $base64Data)) {
            return false;
        }

        // Extract the base64 part
        $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
        $imageData = base64_decode($base64Data, true);
        
        if ($imageData === false) {
            return false;
        }

        // Check file size (5MB limit)
        if (strlen($imageData) > $this->maxFileSize) {
            return false;
        }

        // Validate image format
        $imageInfo = getimagesizefromstring($imageData);
        if ($imageInfo === false) {
            return false;
        }

        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        return in_array($imageInfo['mime'], $allowedMimeTypes);
    }

    private function getExtensionFromMimeType(string $mimeType): ?string
    {
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        return $mimeToExt[$mimeType] ?? null;
    }

    public function getUploadErrors(): array
    {
        return [
            UPLOAD_ERR_OK => 'There is no error, the file uploaded with success',
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the max file size allowed',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the max file size allowed',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
        ];
    }

    /**
     * Clean up uploaded files if there are errors
     */
    public function cleanupUploadedFiles(array $uploadedFiles): void
    {
        foreach ($uploadedFiles as $filePath) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    /**
     * Delete a specific uploaded file
     */
    public function deleteUploadedFile(string $filePath): bool
    {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return true; // File doesn't exist, consider it "deleted"
    }
}
