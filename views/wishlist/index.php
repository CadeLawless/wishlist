<?php
// Display flash messages
if (isset($flash['success'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../images/site-images/menu-close.php');
                echo "</a>
            </div>
            <div class='popup-content'>
                <p><label>" . htmlspecialchars($flash['success']) . "</label></p>
            </div>
        </div>
    </div>";
}

if (isset($flash['error'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../images/site-images/menu-close.php');
                echo "</a>
            </div>
            <div class='popup-content'>
                <p><label>" . htmlspecialchars($flash['error']) . "</label></p>
            </div>
        </div>
    </div>";
}
?>

<h1 class="center"><?php echo $user['name']; ?>'s Wish Lists</h1>
<p class="center" style="margin: 0 0 36px;"><a class="button primary" href="/wishlist/create">Create a New Wish List</a></p>
<div class="wishlist-grid">
    <?php
    if(count($wishlists) > 0){
        foreach($wishlists as $wishlist){
            $id = $wishlist['id'];
            $type = $wishlist['type'];
            $list_name = $wishlist['wishlist_name'];
            $duplicate = $wishlist['duplicate'] == 0 ? "" : " ({$wishlist['duplicate']})";
            $theme_background_id = $wishlist['theme_background_id'];
            $theme_gift_wrap_id = $wishlist['theme_gift_wrap_id'];
            
            $background_image = \App\Services\ThemeService::getBackgroundImage($theme_background_id) ?? "";
            $wrap_image = \App\Services\ThemeService::getGiftWrapImage($theme_gift_wrap_id) ?? "";
            
            echo "
            <a class='wishlist-grid-item' href='/wishlist/$id'>
                <div class='items-list preview' style='";
                echo $background_image == "" ? "" : "background-image: url(images/site-images/themes/desktop-thumbnails/$background_image);";
                echo "'>
                    <div class='item-container'>
                        <img src='images/site-images/themes/gift-wraps/$wrap_image/1.png' class='gift-wrap' alt='gift wrap'>
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
    }else{
        echo "<p style='grid-column: 1 / -1;' class='center'>It doesn't look like you have any wish lists created yet</p>";
    }
    ?>
</div>
