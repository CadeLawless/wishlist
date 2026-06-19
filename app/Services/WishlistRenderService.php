<?php

namespace App\Services;

class WishlistRenderService
{
    /**
     * Generate HTML for a collection of wishlist grid items
     */
    public static function generateWishlistsHtml(array $wishlists, string $context='user', string $search = '', array $user = []): string
    {
        $html = '';
        
        foreach ($wishlists as $wishlist) {
            $id = $wishlist['id'];
            if (isset($wishlist['username'])) {
                $wishlist_username = $wishlist['username'];
            }
            $list_name = htmlspecialchars($wishlist['wishlist_name'], ENT_QUOTES, 'UTF-8');
            $duplicate = "";
            $theme_background_id = $wishlist['theme_background_id'];
            $theme_gift_wrap_id = $wishlist['theme_gift_wrap_id'];
            $secretKey = $wishlist['secret_key'];
            $wishListLink = htmlspecialchars(
                $context === 'public' && (empty($user) || $wishlist['username'] !== $user['username']) ?
                "/buyer/$secretKey?from_friends=true" . ($search !== '' ? '&search=' . urlencode($search) : '') :
                "/wishlists/$id" . ($context === 'public' ? "?from_public=true" : ""));
            
            $background_image = ThemeService::getBackgroundImage($theme_background_id) ?? "";
            $wrap_image = ThemeService::getGiftWrapImage($theme_gift_wrap_id) ?? "";
            $complete = $wishlist['complete'];
            $active = $complete === 'No';
            $visibility = $wishlist['visibility'];
            $public = $visibility === 'Public';
            
            $backgroundStyle = $background_image == "" ? "" : "background-image: url(/public/images/site-images/themes/desktop-thumbnails/$background_image);";
            
            $html .= "
            <div class='wishlist-grid-item' data-wishlist-id='$id' data-visibility='" . htmlspecialchars($visibility, ENT_QUOTES, 'UTF-8') . "' data-complete='$complete'>";
                if (($context === 'user' || ($context === 'public' && !empty($user) && $wishlist_username === $user['username'])) && count($wishlists) > 1) {
                    $html .= "
                    <div class='click-to-select'>Click to Select</div>
                    <button class='wishlist-checkbox'>
                        <div class='checkbox-icon'>";
                        ob_start();
                        require(__DIR__ . '/../../public/images/site-images/icons/checkmark.php');
                        $html .= ob_get_clean();
                        $html .= "</div>
                    </button>";
                    if(!$public) {
                        $html .= "<div class='private-wishlist-icon'>";
                        ob_start();
                        require(__DIR__ . '/../../public/images/site-images/icons/hide-view.php');
                        $html .= ob_get_clean();
                        $html .= "</div>";
                    }
                }
                $html .= "
                <div class='items-list preview" . (!$public ? " private" : "") . "' style='$backgroundStyle'>
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
                <div class='wishlist-grid-item-footer'>
                    <div class='title-row'>
                        <div class='wishlist-name'><span>$list_name$duplicate</span></div>";
                        if ($context === 'user' || ($context === 'public' && !empty($user) && $wishlist_username === $user['username'])) {
                            $html .= self::generateWishListActionMenu(false, $wishlist['wishlist_name'], $id, $active, $public, $visibility);
                        }
                        $html .= "
                    </div>
                    <a class='view-wishlist-button button secondary' href='$wishListLink'>View</a>
                </div>
            </div>";
        }
        
        return $html;
    }

    public static function generateWishListActionMenu(bool $detailed, string $wishlistName, int $wishlistId, bool $active, bool $public, string $visibility, array $items=[], array $other_wishlist_options=[]): string
    {
        $html = "
        <div class='three-dots-menu'>
            <div class='dots-icon'>";
            ob_start();
            require(__DIR__ . '/../../public/images/site-images/icons/three-dots.php');
            $html .= ob_get_clean();
            $html .= "</div>
            <div class='quick-menu'>
                <button class='quick-menu-item rename-wishlist' data-current-name='" . htmlspecialchars($wishlistName, ENT_QUOTES, 'UTF-8') . "'>
                    <span class='menu-icon'>";
                    ob_start();
                    require(__DIR__ . '/../../public/images/site-images/icons/edit.php');
                    $html .= ob_get_clean();
                    $html .= "</span>
                    Rename
                </button>";
                if ($detailed) {
                    $html .= "
                    <button class='quick-menu-item change-theme popup-button' data-wishlist-id='$wishlistId'>
                        <span class='menu-icon'>";
                        ob_start();
                        require(__DIR__ . '/../../public/images/site-images/icons/swap.php');
                        $html .= ob_get_clean();
                        $html .= "</span>
                        Change Theme
                    </button>";

                    if (count($other_wishlist_options) > 0) {
                        $html .= "
                        <button class='quick-menu-item copy-from popup-button' data-wishlist-id='$wishlistId'>
                            <span class='menu-icon'>";
                            ob_start();
                            require(__DIR__ . '/../../public/images/site-images/icons/copy-from.php');
                            $html .= ob_get_clean();
                            $html .= "</span>
                            Copy From...
                        </button>";
                    }

                    if (count($items) > 0) {
                        $html .= "
                        <button class='quick-menu-item copy-to popup-button' data-wishlist-id='$wishlistId'>
                            <span class='menu-icon'>";
                            ob_start();
                            require(__DIR__ . '/../../public/images/site-images/icons/copy-to.php');
                            $html .= ob_get_clean();
                            $html .= "</span>
                            Copy To...
                        </button>";
                    }
                }
                $html .= "
                <button class='quick-menu-item toggle-visibility' data-current-visibility='" . htmlspecialchars($visibility) . "'>
                    <span class='menu-icon'>";
                    ob_start();
                    require(__DIR__ . '/../../public/images/site-images/icons/' . ($public ? 'hide-view.php' : 'view.php'));
                    $html .= ob_get_clean();
                    $html .= "</span>
                    " . ($public ? 'Hide' : 'Make Public') . "
                </button>
                <button class='quick-menu-item toggle-complete' data-current-complete='" . ($active ? 'No' : 'Yes') . "'>
                    <span class='menu-icon'>";
                    ob_start();
                    require(__DIR__ . '/../../public/images/site-images/icons/' . ($active ? 'cancel.php' : 'checkmark.php'));
                    $html .= ob_get_clean();
                    $html .= "</span>
                    " . ($active ? 'Deactivate' : 'Reactivate') . "
                </button>
                <button class='quick-menu-item delete-wishlist' data-wishlist-name='" . htmlspecialchars($wishlistName, ENT_QUOTES, 'UTF-8') . "'>
                    <span class='menu-icon'>";
                    ob_start();
                    require(__DIR__ . '/../../public/images/site-images/icons/delete-trashcan.php');
                    $html .= ob_get_clean();
                    $html .= "</span>
                    Delete
                </button>
            </div>
        </div>";

        return $html;
    }
}
