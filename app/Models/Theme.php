<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Theme extends Model
{
    protected static string $table = 'themes';

    public static function findThemeImage(int $themeId): ?string
    {
        $stmt = Database::query("SELECT theme_image FROM " . static::$table . " WHERE theme_id = ?", [$themeId]);
        $result = $stmt->get_result()->fetch_assoc();
        return $result['theme_image'] ?? null;
    }

    public static function getThemesByType(string $type): array
    {
        $stmt = Database::query("SELECT * FROM " . static::$table . " WHERE type = ?", [$type]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}