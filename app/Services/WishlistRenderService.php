<?php

namespace App\Services;

class WishlistRenderService
{
    /**
     * Generate HTML for a collection of wishlist grid items
     */
    public static function generateWishlistsHtml(array $wishlists): string
    {
        $html = '';
        
        foreach ($wishlists as $wishlist) {
            $id = $wishlist['id'];
            $list_name = htmlspecialchars($wishlist['wishlist_name'], ENT_QUOTES, 'UTF-8');
            $duplicate = $wishlist['duplicate'] == 0 ? "" : " ({$wishlist['duplicate']})";
            $theme_background_id = $wishlist['theme_background_id'];
            $theme_gift_wrap_id = $wishlist['theme_gift_wrap_id'];
            
            $background_image = ThemeService::getBackgroundImage($theme_background_id) ?? "";
            $wrap_image = ThemeService::getGiftWrapImage($theme_gift_wrap_id) ?? "";
            
            $backgroundStyle = $background_image == "" ? "" : "background-image: url(public/images/site-images/themes/desktop-thumbnails/$background_image);";
            
            $html .= "
            <a class='wishlist-grid-item' href='/wishlists/$id'>
                <div class='items-list preview' style='$backgroundStyle'>
                    <div class='item-container'>
                        <img src='/public/images/site-images/themes/gift-wraps/$wrap_image/1.png' class='gift-wrap' alt='gift wrap'>
                        <div class='item-description'>
                            <div class='bar title'></div>
                            <div class='bar'></div>
                            <div class='bar'></div>
                            <div class='bar'></div>
                            <div class='bar'></div>
                        </div>
                    </div>
                </div>
                <div class='wishlist-overlay'></div>
                <div class='wishlist-name'><span>$list_name$duplicate</span></div>
            </a>";
        }
        
        return $html;
    }
}
