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

    /**
     * Get the temp upload directory path (absolute path)
     */
    public static function getTempUploadPath(): string
    {
        $path = self::getBaseUploadPath() . 'temp' . DIRECTORY_SEPARATOR;
        // Ensure temp directory exists
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
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

    /**
     * Upload item image to temporary folder (for validation)
     * 
     * @param array $file The uploaded file array
     * @param string $itemName The item name for filename generation
     * @return array Result with success, filename, filepath, error
     */
    public function uploadItemImageToTemp(array $file, string $itemName): array
    {
        $result = [
            'success' => false,
            'filename' => null,
            'filepath' => null,
            'error' => null,
            'is_temp' => true
        ];

        // Validate file
        if (!$this->validateFile($file)) {
            $result['error'] = Constants::ERROR_INVALID_FILE;
            return $result;
        }

        // Create temp directory if it doesn't exist
        $uploadDir = self::getTempUploadPath();
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Failed to create temp upload directory: {$uploadDir}");
                $result['error'] = Constants::ERROR_DIRECTORY_CREATION;
                return $result;
            }
        }

        // Generate unique filename with session ID and timestamp
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $sessionId = session_id();
        $timestamp = time();
        $filename = $this->sanitizeFilename($itemName) . '_' . substr($sessionId, 0, 8) . '_' . $timestamp . '.' . $extension;

        // Handle filename conflicts
        $filename = $this->getUniqueFilename($uploadDir, $filename);

        // Move uploaded file to temp
        $targetPath = $uploadDir . $filename;
        $moveResult = move_uploaded_file($file['tmp_name'], $targetPath);
        
        if ($moveResult && file_exists($targetPath)) {
            // Optimize image if needed
            $this->optimizeImage($targetPath);
            
            $result['success'] = true;
            $result['filename'] = $filename;
            $result['filepath'] = $targetPath;
        } else {
            $result['error'] = 'Failed to upload file to temporary folder. Please try again.';
        }

        return $result;
    }

    /**
     * Upload base64 image to temporary folder (for validation)
     * 
     * @param string $base64Data The base64 image data
     * @param string $itemName The item name for filename generation
     * @return array Result with success, filename, filepath, error
     */
    public function uploadFromBase64ToTemp(string $base64Data, string $itemName): array
    {
        $result = [
            'success' => false,
            'filename' => null,
            'filepath' => null,
            'error' => null,
            'is_temp' => true
        ];

        // Validate base64 data
        if (!$this->validateBase64Image($base64Data)) {
            $result['error'] = 'Invalid image data. Please paste a valid image.';
            return $result;
        }

        // Create temp directory if it doesn't exist
        $uploadDir = self::getTempUploadPath();
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Failed to create temp upload directory for base64: {$uploadDir}");
                $result['error'] = 'Failed to create temp upload directory. Please check permissions.';
                return $result;
            }
        }

        // Handle both full data URL format and raw base64
        $base64ToDecode = $base64Data;
        if (preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,/', $base64Data)) {
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

        // Generate unique filename with session ID and timestamp
        $sessionId = session_id();
        $timestamp = time();
        $filename = $this->sanitizeFilename($itemName) . '_' . substr($sessionId, 0, 8) . '_' . $timestamp . '.' . $extension;
        $filename = $this->getUniqueFilename($uploadDir, $filename);

        // Save image to temp
        $targetPath = $uploadDir . $filename;
        if (file_put_contents($targetPath, $imageData)) {
            // Optimize image if needed
            $this->optimizeImage($targetPath);
            
            $result['success'] = true;
            $result['filename'] = $filename;
            $result['filepath'] = $targetPath;
        } else {
            $result['error'] = 'Failed to save image to temporary folder. Please try again.';
        }

        return $result;
    }

    /**
     * Upload image from URL to temporary folder (for validation)
     * 
     * @param string $imageUrl The URL of the image to download
     * @param string $itemName The item name for filename generation
     * @return array Result with success, filename, filepath, error
     */
    public function uploadFromUrlToTemp(string $imageUrl, string $itemName): array
    {
        $result = [
            'success' => false,
            'filename' => null,
            'filepath' => null,
            'error' => null,
            'is_temp' => true
        ];

        // Validate URL
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $result['error'] = 'Invalid image URL.';
            return $result;
        }

        // Create temp directory if it doesn't exist
        $uploadDir = self::getTempUploadPath();
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Failed to create temp upload directory for URL: {$uploadDir}");
                $result['error'] = 'Failed to create temp upload directory. Please check permissions.';
                return $result;
            }
        }

        // Download image using cURL
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
            CURLOPT_ENCODING => '',
        ]);

        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($imageData === false || $httpCode !== 200 || !empty($error)) {
            $result['error'] = 'Failed to download image from URL. The image may be inaccessible or blocked.';
            return $result;
        }

        // Check file size
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

        // Generate unique filename with session ID and timestamp
        $sessionId = session_id();
        $timestamp = time();
        $filename = $this->sanitizeFilename($itemName) . '_' . substr($sessionId, 0, 8) . '_' . $timestamp . '.' . $extension;
        $filename = $this->getUniqueFilename($uploadDir, $filename);

        // Save image to temp
        $targetPath = $uploadDir . $filename;
        if (file_put_contents($targetPath, $imageData)) {
            // Optimize image if needed
            $this->optimizeImage($targetPath);
            
            $result['success'] = true;
            $result['filename'] = $filename;
            $result['filepath'] = $targetPath;
        } else {
            $result['error'] = 'Failed to save downloaded image to temporary folder. Please try again.';
        }

        return $result;
    }

    /**
     * Move temporary image to final destination
     * 
     * @param string $tempFilename The temporary filename
     * @param string $wishlistId The wishlist ID for the final destination
     * @param string $itemName The item name for filename generation
     * @return array Result with success, filename, error
     */
    public function moveTempToFinal(string $tempFilename, string $wishlistId, string $itemName): array
    {
        $result = [
            'success' => false,
            'filename' => null,
            'error' => null
        ];

        $tempPath = self::getTempUploadPath() . $tempFilename;
        
        if (!file_exists($tempPath)) {
            $result['error'] = 'Temporary file not found.';
            return $result;
        }

        // Create final directory if it doesn't exist
        $finalDir = self::getBaseUploadPath() . "{$wishlistId}" . DIRECTORY_SEPARATOR;
        if (!is_dir($finalDir)) {
            if (!mkdir($finalDir, 0755, true)) {
                error_log("Failed to create final upload directory: {$finalDir}");
                $result['error'] = Constants::ERROR_DIRECTORY_CREATION;
                return $result;
            }
        }

        // Generate final filename (without session ID/timestamp from temp)
        $extension = pathinfo($tempFilename, PATHINFO_EXTENSION);
        $timestamp = date('Y-m-d_H-i-s');
        $finalFilename = $this->sanitizeFilename($itemName) . '_' . $timestamp . '.' . $extension;
        $finalFilename = $this->getUniqueFilename($finalDir, $finalFilename);

        // Move file from temp to final location
        $finalPath = $finalDir . $finalFilename;
        if (rename($tempPath, $finalPath)) {
            $result['success'] = true;
            $result['filename'] = $finalFilename;
        } else {
            $result['error'] = 'Failed to move temporary file to final location.';
        }

        return $result;
    }

    /**
     * Delete temporary image file
     * 
     * @param string $tempFilename The temporary filename
     * @return bool Success status
     */
    public function deleteTempImage(string $tempFilename): bool
    {
        $tempPath = self::getTempUploadPath() . $tempFilename;
        if (file_exists($tempPath)) {
            return unlink($tempPath);
        }
        return true; // File doesn't exist, consider it "deleted"
    }

    /**
     * Clean up old temporary files (older than specified hours)
     * 
     * @param int $hoursOld Files older than this many hours will be deleted (default: 24)
     * @return int Number of files deleted
     */
    public function cleanupOldTempFiles(int $hoursOld = 24): int
    {
        $tempDir = self::getTempUploadPath();
        $deletedCount = 0;
        $cutoffTime = time() - ($hoursOld * 3600);

        if (!is_dir($tempDir)) {
            return 0;
        }

        $files = scandir($tempDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $tempDir . $file;
            if (is_file($filePath)) {
                // Check file modification time
                if (filemtime($filePath) < $cutoffTime) {
                    if (unlink($filePath)) {
                        $deletedCount++;
                    }
                }
            }
        }

        return $deletedCount;
    }

    /**
     * Get the public URL path for a temporary image (for preview)
     * 
     * @param string $tempFilename The temporary filename
     * @return string Public URL path
     */
    public static function getTempImageUrl(string $tempFilename): string
    {
        return '/public/images/item-images/temp/' . $tempFilename;
    }

    /**
     * Get the theme images base directory path
     * 
     * @return string Absolute path to themes directory
     */
    public static function getThemesBasePath(): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'site-images' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR;
    }

    /**
     * Upload background image (desktop or mobile)
     * 
     * @param array $file The uploaded file array
     * @param string $imageName The base image name (without extension)
     * @param string $type 'desktop' or 'mobile'
     * @return array Result with success, filename, error
     */
    public function uploadBackgroundImage(array $file, string $imageName, string $type = 'desktop'): array
    {
        $result = [
            'success' => false,
            'filename' => null,
            'error' => null
        ];

        // Validate file
        if (!$this->validateFile($file)) {
            $result['error'] = Constants::ERROR_INVALID_FILE;
            return $result;
        }

        // Determine target directory
        $uploadDir = self::getThemesBasePath() . ($type === 'desktop' ? 'desktop-backgrounds' : 'mobile-backgrounds') . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Failed to create background upload directory: {$uploadDir}");
                $result['error'] = 'Failed to create upload directory.';
                return $result;
            }
        }

        // Generate filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $this->sanitizeFilename($imageName) . '.' . $extension;

        // Move uploaded file
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Optimize image
            $this->optimizeImage($targetPath);
            
            $result['success'] = true;
            $result['filename'] = $filename;
        } else {
            $result['error'] = 'Failed to upload file.';
        }

        return $result;
    }

    /**
     * Upload and resize thumbnail directly
     * 
     * @param array $file The uploaded file array
     * @param string $imageName The base image name (without extension)
     * @param string $type 'desktop' or 'mobile'
     * @param int $maxWidth Maximum thumbnail width (default: 200)
     * @param int $maxHeight Maximum thumbnail height (default: 200)
     * @return array Result with success, filename, error
     */
    public function uploadBackgroundThumbnail(array $file, string $imageName, string $type = 'desktop', int $maxWidth = 200, int $maxHeight = 200): array
    {
        $result = [
            'success' => false,
            'filename' => null,
            'error' => null
        ];

        // Validate file
        if (!$this->validateFile($file)) {
            $result['error'] = Constants::ERROR_INVALID_FILE;
            return $result;
        }

        // Determine target directory
        $uploadDir = self::getThemesBasePath() . ($type === 'desktop' ? 'desktop-thumbnails' : 'mobile-thumbnails') . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Failed to create thumbnail upload directory: {$uploadDir}");
                $result['error'] = 'Failed to create upload directory.';
                return $result;
            }
        }

        // Get image info from uploaded file
        $sourcePath = $file['tmp_name'];
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            $result['error'] = 'Invalid image file.';
            return $result;
        }

        $mimeType = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        // Calculate thumbnail dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        // Create image resource
        $source = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => false
        };

        if (!$source) {
            $result['error'] = 'Failed to create image resource.';
            return $result;
        }

        // Create thumbnail
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG
        if ($mimeType === 'image/png') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 0, 0, 0, 127);
            imagefill($thumbnail, 0, 0, $transparent);
        }

        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Generate filename - use same extension as source image for consistency
        // First try to match existing background extension, otherwise use uploaded file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Check if a background with same base name exists to match extension
        $backgroundDir = self::getThemesBasePath() . ($type === 'desktop' ? 'desktop-backgrounds' : 'mobile-backgrounds') . DIRECTORY_SEPARATOR;
        $existingFiles = glob($backgroundDir . $imageName . '.*');
        if (!empty($existingFiles)) {
            $existingExtension = strtolower(pathinfo($existingFiles[0], PATHINFO_EXTENSION));
            if (in_array($existingExtension, ['png', 'jpg', 'jpeg', 'webp'])) {
                $extension = $existingExtension;
            }
        }
        
        $filename = $this->sanitizeFilename($imageName) . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        // Save thumbnail
        $success = match ($mimeType) {
            'image/jpeg' => imagejpeg($thumbnail, $targetPath, 85),
            'image/png' => imagepng($thumbnail, $targetPath, 8),
            'image/webp' => imagewebp($thumbnail, $targetPath, 85),
            default => false
        };

        // Clean up
        imagedestroy($source);
        imagedestroy($thumbnail);

        if ($success) {
            $result['success'] = true;
            $result['filename'] = $filename;
        } else {
            $result['error'] = 'Failed to save thumbnail.';
        }

        return $result;
    }

    /**
     * Create thumbnail from background image
     * 
     * @param string $sourceFilename The source image filename
     * @param string $imageName The base image name (without extension)
     * @param string $type 'desktop' or 'mobile'
     * @param int $maxWidth Maximum thumbnail width (default: 200)
     * @param int $maxHeight Maximum thumbnail height (default: 200)
     * @return array Result with success, filename, error
     */
    public function createBackgroundThumbnail(string $sourceFilename, string $imageName, string $type = 'desktop', int $maxWidth = 200, int $maxHeight = 200): array
    {
        $result = [
            'success' => false,
            'filename' => null,
            'error' => null
        ];

        // Determine source and target directories
        $sourceDir = self::getThemesBasePath() . ($type === 'desktop' ? 'desktop-backgrounds' : 'mobile-backgrounds') . DIRECTORY_SEPARATOR;
        $targetDir = self::getThemesBasePath() . ($type === 'desktop' ? 'desktop-thumbnails' : 'mobile-thumbnails') . DIRECTORY_SEPARATOR;
        
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                error_log("Failed to create thumbnail directory: {$targetDir}");
                $result['error'] = 'Failed to create thumbnail directory.';
                return $result;
            }
        }

        $sourcePath = $sourceDir . $sourceFilename;
        if (!file_exists($sourcePath)) {
            $result['error'] = 'Source image not found.';
            return $result;
        }

        // Get image info
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            $result['error'] = 'Invalid image file.';
            return $result;
        }

        $mimeType = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        // Calculate thumbnail dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        // Create image resource
        $source = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => false
        };

        if (!$source) {
            $result['error'] = 'Failed to create image resource.';
            return $result;
        }

        // Create thumbnail
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG
        if ($mimeType === 'image/png') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 0, 0, 0, 127);
            imagefill($thumbnail, 0, 0, $transparent);
        }

        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save thumbnail
        $extension = pathinfo($sourceFilename, PATHINFO_EXTENSION);
        $thumbnailFilename = $this->sanitizeFilename($imageName) . '.' . $extension;
        $targetPath = $targetDir . $thumbnailFilename;

        $success = match ($mimeType) {
            'image/jpeg' => imagejpeg($thumbnail, $targetPath, 85),
            'image/png' => imagepng($thumbnail, $targetPath, 8),
            'image/webp' => imagewebp($thumbnail, $targetPath, 85),
            default => false
        };

        // Clean up
        imagedestroy($source);
        imagedestroy($thumbnail);

        if ($success) {
            $result['success'] = true;
            $result['filename'] = $thumbnailFilename;
        } else {
            $result['error'] = 'Failed to save thumbnail.';
        }

        return $result;
    }

    /**
     * Upload gift wrap image to a gift wrap set
     * 
     * @param array $file The uploaded file array
     * @param string $giftWrapFolder The gift wrap folder name
     * @return array Result with success, filename, error
     */
    public function uploadGiftWrapImage(array $file, string $giftWrapFolder): array
    {
        $result = [
            'success' => false,
            'filename' => null,
            'error' => null
        ];

        // Validate file
        if (!$this->validateFile($file)) {
            $result['error'] = Constants::ERROR_INVALID_FILE;
            return $result;
        }

        // Determine target directory
        $uploadDir = self::getThemesBasePath() . 'gift-wraps' . DIRECTORY_SEPARATOR . $giftWrapFolder . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Failed to create gift wrap upload directory: {$uploadDir}");
                $result['error'] = 'Failed to create upload directory.';
                return $result;
            }
        }

        // Find next available number
        $nextNumber = $this->getNextGiftWrapNumber($uploadDir);

        // Generate filename (always PNG for gift wraps)
        $filename = $nextNumber . '.png';

        // Convert to PNG if needed
        $sourcePath = $file['tmp_name'];
        $targetPath = $uploadDir . $filename;

        // Convert image to PNG
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            $result['error'] = 'Invalid image file.';
            return $result;
        }

        $mimeType = $imageInfo['mime'];
        $source = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => false
        };

        if (!$source) {
            $result['error'] = 'Failed to process image.';
            return $result;
        }

        // Save as PNG
        imagealphablending($source, false);
        imagesavealpha($source, true);
        
        if (imagepng($source, $targetPath, 8)) {
            $this->optimizeImage($targetPath);
            $result['success'] = true;
            $result['filename'] = $filename;
        } else {
            $result['error'] = 'Failed to save image.';
        }

        imagedestroy($source);

        return $result;
    }

    /**
     * Get next available gift wrap number in a folder
     * 
     * @param string $folderPath The gift wrap folder path
     * @return int Next available number
     */
    private function getNextGiftWrapNumber(string $folderPath): int
    {
        if (!is_dir($folderPath)) {
            return 1;
        }

        $files = glob($folderPath . '*.png');
        $numbers = [];
        
        foreach ($files as $file) {
            $basename = basename($file);
            $number = (int)pathinfo($basename, PATHINFO_FILENAME);
            if ($number > 0) {
                $numbers[] = $number;
            }
        }

        if (empty($numbers)) {
            return 1;
        }

        return max($numbers) + 1;
    }

    /**
     * Delete gift wrap image
     * 
     * @param string $giftWrapFolder The gift wrap folder name
     * @param string $filename The filename to delete
     * @return bool Success status
     */
    public function deleteGiftWrapImage(string $giftWrapFolder, string $filename): bool
    {
        $filePath = self::getThemesBasePath() . 'gift-wraps' . DIRECTORY_SEPARATOR . $giftWrapFolder . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return true;
    }

    /**
     * Reorder gift wrap images by renaming files
     * 
     * @param string $giftWrapFolder The gift wrap folder name
     * @param array $newOrder Array of filenames in new order (e.g., ['3.png', '1.png', '2.png'])
     * @return bool Success status
     */
    public function reorderGiftWrapImages(string $giftWrapFolder, array $newOrder): bool
    {
        $folderPath = self::getThemesBasePath() . 'gift-wraps' . DIRECTORY_SEPARATOR . $giftWrapFolder . DIRECTORY_SEPARATOR;
        
        if (!is_dir($folderPath)) {
            return false;
        }

        // Create temporary names to avoid conflicts
        $tempPrefix = 'temp_' . time() . '_';
        foreach ($newOrder as $index => $filename) {
            $oldPath = $folderPath . $filename;
            $newTempPath = $folderPath . $tempPrefix . $filename;
            if (file_exists($oldPath)) {
                rename($oldPath, $newTempPath);
            }
        }

        // Rename to final numbers
        foreach ($newOrder as $index => $filename) {
            $newNumber = $index + 1;
            $tempPath = $folderPath . $tempPrefix . $filename;
            $finalPath = $folderPath . $newNumber . '.png';
            
            if (file_exists($tempPath)) {
                rename($tempPath, $finalPath);
            }
        }

        return true;
    }

    /**
     * Get all gift wrap images for a folder
     * 
     * @param string $giftWrapFolder The gift wrap folder name
     * @return array Array of image filenames sorted by number
     */
    public function getGiftWrapImages(string $giftWrapFolder): array
    {
        $folderPath = self::getThemesBasePath() . 'gift-wraps' . DIRECTORY_SEPARATOR . $giftWrapFolder . DIRECTORY_SEPARATOR;
        
        if (!is_dir($folderPath)) {
            return [];
        }

        $files = glob($folderPath . '*.png');
        $images = [];
        
        foreach ($files as $file) {
            $basename = basename($file);
            $number = (int)pathinfo($basename, PATHINFO_FILENAME);
            if ($number > 0) {
                $images[$number] = $basename;
            }
        }

        ksort($images);
        return array_values($images);
    }

    /**
     * Delete background image and its thumbnails
     * 
     * @param string $imageName The image name (without extension)
     * @return bool Success status
     */
    public function deleteBackgroundImages(string $imageName): bool
    {
        $basePath = self::getThemesBasePath();
        $deleted = true;

        // Try to delete all variants (with different extensions)
        $extensions = ['png', 'jpg', 'jpeg', 'webp'];
        $directories = ['desktop-backgrounds', 'desktop-thumbnails', 'mobile-backgrounds', 'mobile-thumbnails'];

        foreach ($directories as $dir) {
            foreach ($extensions as $ext) {
                $filePath = $basePath . $dir . DIRECTORY_SEPARATOR . $imageName . '.' . $ext;
                if (file_exists($filePath)) {
                    if (!unlink($filePath)) {
                        $deleted = false;
                    }
                }
            }
        }

        return $deleted;
    }
}
