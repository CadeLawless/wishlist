<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Constants;

class FileUploadService
{
    private readonly array $allowedTypes;
    private readonly int $maxFileSize;

    public function __construct()
    {
        $this->allowedTypes = Constants::ALLOWED_IMAGE_TYPES;
        $this->maxFileSize = Constants::MAX_FILE_SIZE_BYTES;
    }

    /**
     * Get the base upload directory path (absolute path)
     */
    public static function getBaseUploadPath(): string
    {
        // Get the project root directory (one level up from public/)
        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'item-images' . DIRECTORY_SEPARATOR;
        return $path;
    }

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
            $result['error'] = Constants::ERROR_INVALID_FILE;
            return $result;
        }

        // Create directory if it doesn't exist
        $uploadDir = self::getBaseUploadPath() . "{$wishlistId}" . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Failed to create upload directory: {$uploadDir}");
                $result['error'] = Constants::ERROR_DIRECTORY_CREATION;
                return $result;
            }
        }

        // Generate filename with timestamp to ensure uniqueness
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $timestamp = date('Y-m-d_H-i-s');
        $filename = $this->sanitizeFilename($itemName) . '_' . $timestamp . '.' . $extension;

        // Handle filename conflicts
        $filename = $this->getUniqueFilename($uploadDir, $filename);

        // Move uploaded file
        $targetPath = $uploadDir . $filename;
        $moveResult = move_uploaded_file($file['tmp_name'], $targetPath);
        
        if ($moveResult && file_exists($targetPath)) {
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
        $targetDir = self::getBaseUploadPath() . "{$targetWishlistId}" . DIRECTORY_SEPARATOR;
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                error_log("Failed to create target directory: {$targetDir}");
                $result['error'] = 'Failed to create target directory. Please check permissions.';
                return $result;
            }
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
        $filePath = self::getBaseUploadPath() . "{$wishlistId}" . DIRECTORY_SEPARATOR . "{$filename}";
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
        $uploadDir = self::getBaseUploadPath() . "{$wishlistId}" . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Failed to create upload directory for base64: {$uploadDir}");
                $result['error'] = 'Failed to create upload directory. Please check permissions.';
                return $result;
            }
        }

        // Handle both full data URL format and raw base64 (same as validateBase64Image)
        $base64ToDecode = $base64Data;
        if (preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,/', $base64Data)) {
            // Full data URL format - extract the base64 part
            $base64ToDecode = substr($base64Data, strpos($base64Data, ',') + 1);
        }
        
        // Decode base64 data
        $imageData = base64_decode($base64ToDecode, true);
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

        // Generate filename with timestamp to ensure uniqueness
        $timestamp = date('Y-m-d_H-i-s');
        $filename = $this->sanitizeFilename($itemName) . '_' . $timestamp . '.' . $extension;
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

    public function updateCopiedItemImages(string $copyId, string $oldImage, string $newImage, string $sourceWishlistId): bool
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

                // Copy new image to this wishlist from the source wishlist
                $sourcePath = self::getBaseUploadPath() . "{$sourceWishlistId}" . DIRECTORY_SEPARATOR . "{$newImage}";
                $targetDir = self::getBaseUploadPath() . "{$wishlistId}" . DIRECTORY_SEPARATOR;
                
                if (!is_dir($targetDir)) {
                    if (!mkdir($targetDir, 0755, true)) {
                        error_log("Failed to create target directory for copied image: {$targetDir}");
                        continue; // Skip this item if directory creation fails
                    }
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
            $source = match ($mimeType) {
                'image/jpeg' => imagecreatefromjpeg($filePath),
                'image/png' => imagecreatefrompng($filePath),
                'image/webp' => imagecreatefromwebp($filePath),
                default => false
            };

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
            $success = match ($mimeType) {
                'image/jpeg' => imagejpeg($resized, $filePath, 85), // 85% quality
                'image/png' => imagepng($resized, $filePath, 8), // 8 compression level
                'image/webp' => imagewebp($resized, $filePath, 85), // 85% quality
                default => false
            };

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
        // Handle both full data URL format and raw base64
        if (preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,/', $base64Data)) {
            // Full data URL format - extract the base64 part
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
        }
        // If it doesn't match the data URL format, assume it's raw base64

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

    /**
     * Download and save image from URL
     * 
     * @param string $imageUrl The URL of the image to download
     * @param string $wishlistId The wishlist ID for the directory
     * @param string $itemName The item name for filename generation
     * @return array Result with success, filename, filepath, error
     */
    public function uploadFromUrl(string $imageUrl, string $wishlistId, string $itemName): array
    {
        $result = [
            'success' => false,
            'filename' => null,
            'filepath' => null,
            'error' => null
        ];

        // Validate URL
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $result['error'] = 'Invalid image URL.';
            return $result;
        }

        // Create directory if it doesn't exist
        $uploadDir = self::getBaseUploadPath() . "{$wishlistId}" . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Failed to create upload directory for URL: {$uploadDir}");
                $result['error'] = 'Failed to create upload directory. Please check permissions.';
                return $result;
            }
        }

        // Download image using cURL (more reliable for external URLs)
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $imageUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => Constants::CURL_MAX_REDIRECTS,
            CURLOPT_TIMEOUT => Constants::CURL_TIMEOUT,
            CURLOPT_USERAGENT => Constants::DEFAULT_USER_AGENT,
            CURLOPT_HTTPHEADER => [
                'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive',
                'Cache-Control: no-cache'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING => '', // Let cURL handle encoding
        ]);

        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($imageData === false || $httpCode !== 200 || !empty($error)) {
            $result['error'] = 'Failed to download image from URL. The image may be inaccessible or blocked.';
            return $result;
        }

        // Check file size (5MB limit)
        if (strlen($imageData) > $this->maxFileSize) {
            $result['error'] = 'Image file is too large. Maximum size is 5MB.';
            return $result;
        }

        // Validate image format
        $imageInfo = getimagesizefromstring($imageData);
        if ($imageInfo === false) {
            $result['error'] = 'Invalid image format. Please use JPG, PNG, or WEBP.';
            return $result;
        }

        $mimeType = $imageInfo['mime'];
        $extension = $this->getExtensionFromMimeType($mimeType);
        
        if (!$extension) {
            $result['error'] = 'Unsupported image format. Please use JPG, PNG, or WEBP.';
            return $result;
        }

        // Generate filename with timestamp to ensure uniqueness
        $timestamp = date('Y-m-d_H-i-s');
        $filename = $this->sanitizeFilename($itemName) . '_' . $timestamp . '.' . $extension;
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
            $result['error'] = 'Failed to save downloaded image. Please try again.';
        }

        return $result;
    }
}
