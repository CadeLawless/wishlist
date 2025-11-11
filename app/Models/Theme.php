<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Theme extends Model
{
    protected static string $table = 'themes';
    protected static string $primaryKey = 'theme_id';

    public static function findThemeImage(int $themeId): ?string
    {
        $stmt = Database::query("SELECT theme_image FROM " . static::$table . " WHERE theme_id = ?", [$themeId]);
        $result = $stmt->get_result()->fetch_assoc();
        return $result['theme_image'] ?? null;
    }

    public static function getThemesByType(string $themeType): array
    {
        $stmt = Database::query("SELECT * FROM " . static::$table . " WHERE theme_type = ? ORDER BY theme_name ASC", [$themeType]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function paginateByType(string $themeType, int $perPage, int $offset): array
    {
        $stmt = Database::query(
            "SELECT * FROM " . static::$table . " WHERE theme_type = ? ORDER BY theme_name ASC LIMIT ? OFFSET ?",
            [$themeType, $perPage, $offset]
        );
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function countByType(string $themeType): int
    {
        $stmt = Database::query("SELECT COUNT(*) as count FROM " . static::$table . " WHERE theme_type = ?", [$themeType]);
        $result = $stmt->get_result()->fetch_assoc();
        return (int)$result['count'];
    }
}