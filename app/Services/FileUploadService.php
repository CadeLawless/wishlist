<?php

namespace App\Services;

class FileUploadService
{
    private array $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    private int $maxFileSize = 5 * 1024 * 1024; // 5MB

    public function uploadItemImage(array $file, string $wishlistId, string $itemName): array
    {
        $result = [
            'success' => false,
            'filename' => null,
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
            $result['success'] = true;
            $result['filename'] = $filename;
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

        // Additional MIME type check
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
}
