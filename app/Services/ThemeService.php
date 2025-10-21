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
     * @return array Array of background theme data
     */
    public static function getBackgroundThemes(): array
    {
        $stmt = Database::query("SELECT * FROM themes WHERE theme_type = 'background' ORDER BY theme_name ASC");
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get gift wrap themes only
     * 
     * @return array Array of gift wrap theme data
     */
    public static function getGiftWrapThemes(): array
    {
        $stmt = Database::query("SELECT * FROM themes WHERE theme_type = 'gift_wrap' ORDER BY theme_name ASC");
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}