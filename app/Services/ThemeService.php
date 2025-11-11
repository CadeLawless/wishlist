<?php

namespace App\Services;

use App\Core\Database;

class ThemeService
{
    /**
     * Get background image filename by theme ID
     * 
     * @param int $themeId The theme ID
     * @return string|null The theme image filename or null if not found
     */
    public static function getBackgroundImage(int $themeId): ?string
    {
        if ($themeId == 0) {
            return null;
        }
        
        $stmt = Database::query("SELECT theme_image FROM themes WHERE theme_id = ?", [$themeId]);
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result ? $result['theme_image'] : null;
    }
    
    /**
     * Get gift wrap image filename by theme ID
     * 
     * @param int $themeId The theme ID
     * @return string|null The theme image filename or null if not found
     */
    public static function getGiftWrapImage(int $themeId): ?string
    {
        if ($themeId == 0) {
            return null;
        }
        
        $stmt = Database::query("SELECT theme_image FROM themes WHERE theme_id = ?", [$themeId]);
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result ? $result['theme_image'] : null;
    }
    
    /**
     * Get all available themes
     * 
     * @return array Array of theme data
     */
    public static function getAllThemes(): array
    {
        $stmt = Database::query("SELECT * FROM themes ORDER BY theme_name ASC");
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get background themes only
     * 
     * @param string $type The theme type (Birthday/Christmas)
     * @return array Array of background theme data
     */
    public static function getBackgroundThemes(?string $type = null): array
    {
        if ($type) {
            $stmt = Database::query("SELECT * FROM themes WHERE theme_type = 'Background' AND theme_tag = ? ORDER BY theme_name ASC", [$type]);
        } else {
            $stmt = Database::query("SELECT * FROM themes WHERE theme_type = 'Background' ORDER BY theme_name ASC");
        }
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get gift wrap themes only
     * 
     * @param string $type The theme type (Birthday/Christmas)
     * @return array Array of gift wrap theme data
     */
    public static function getGiftWrapThemes(?string $type = null): array
    {
        if ($type) {
            $stmt = Database::query("SELECT * FROM themes WHERE theme_type = 'Gift Wrap' AND theme_tag = ? ORDER BY theme_name ASC", [$type]);
        } else {
            $stmt = Database::query("SELECT * FROM themes WHERE theme_type = 'Gift Wrap' ORDER BY theme_name ASC");
        }
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get the number of gift wrap files for a theme
     * 
     * @param string $wrapImage The gift wrap image name
     * @return int Number of gift wrap files
     */
    public static function getGiftWrapFileCount(string $wrapImage): int
    {
        $count = 0;
        $basePath = __DIR__ . '/../../public/images/site-images/themes/gift-wraps/' . $wrapImage . '/';
        
        if (is_dir($basePath)) {
            $files = glob($basePath . '*.png');
            $count = count($files);
        }
        
        return $count;
    }
}