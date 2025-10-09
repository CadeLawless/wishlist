<?php

namespace App\Models;

use App\Core\Model;

class Theme extends Model
{
    protected string $table = 'themes';
    protected array $fillable = [
        'theme_id', 'theme_image', 'theme_type'
    ];

    public function getBackgroundPath(): string
    {
        return "images/site-images/themes/desktop-backgrounds/{$this->theme_image}";
    }

    public function getMobileBackgroundPath(): string
    {
        return "images/site-images/themes/mobile-backgrounds/{$this->theme_image}";
    }

    public function getThumbnailPath(): string
    {
        return "images/site-images/themes/desktop-thumbnails/{$this->theme_image}";
    }

    public function getMobileThumbnailPath(): string
    {
        return "images/site-images/themes/mobile-thumbnails/{$this->theme_image}";
    }

    public function getGiftWrapPath(): string
    {
        return "images/site-images/themes/gift-wraps/{$this->theme_image}";
    }

    public function getGiftWrapThumbnailPath(): string
    {
        return "images/site-images/themes/gift-wraps/{$this->theme_image}";
    }

    public function isBackground(): bool
    {
        return $this->theme_type === 'background';
    }

    public function isGiftWrap(): bool
    {
        return $this->theme_type === 'gift_wrap';
    }

    public function getDisplayName(): string
    {
        // Remove file extension and format name
        $name = pathinfo($this->theme_image, PATHINFO_FILENAME);
        return ucwords(str_replace(['-', '_'], ' ', $name));
    }
}
