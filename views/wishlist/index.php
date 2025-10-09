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
            
            $background_image = "";
            if($theme_background_id != 0){
                // Get theme image from database
                $stmt = \App\Core\Database::query("SELECT theme_image FROM themes WHERE theme_id = ?", [$theme_background_id]);
                $theme = $stmt->get_result()->fetch_assoc();
                if($theme) {
                    $background_image = $theme['theme_image'];
                }
            }
            
            $wrap_image = "";
            if($theme_gift_wrap_id != 0){
                // Get gift wrap image from database
                $stmt = \App\Core\Database::query("SELECT theme_image FROM themes WHERE theme_id = ?", [$theme_gift_wrap_id]);
                $wrap_theme = $stmt->get_result()->fetch_assoc();
                if($wrap_theme) {
                    $wrap_image = $wrap_theme['theme_image'];
                }
            }
            
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
