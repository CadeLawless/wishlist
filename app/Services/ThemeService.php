<?php

namespace App\Services;

use App\Core\Database;

class ThemeService
{
    public static function getThemesByType(string $type, string $themeType): array
    {
        $sql = "SELECT * FROM themes WHERE theme_type = ? AND theme_tag = ? ORDER BY theme_name ASC";
        $stmt = Database::query($sql, [$themeType, $type]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public static function getGiftWrapThemes(string $type): array
    {
        return self::getThemesByType($type, 'Gift Wrap');
    }
    
    public static function getBackgroundThemes(string $type): array
    {
        return self::getThemesByType($type, 'Background');
    }
    
    public static function getGiftWrapFileCount(string $wrapImage): int
    {
        $path = "images/site-images/themes/gift-wraps/$wrapImage";
        if (is_dir($path)) {
            $iterator = new \FilesystemIterator($path, \FilesystemIterator::SKIP_DOTS);
            return iterator_count($iterator);
        }
        return 0;
    }
}
