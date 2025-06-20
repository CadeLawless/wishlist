<?php

namespace App\Models;

use Core\Model;
use Helpers\FormField;
use App\Models\User;
use Helpers\StringFunctions;

class WishList extends Model
{
    protected string $table = 'wishlists';
    protected string $siteImageFolderWebPath = "/wishlist/public/assets/images/site-images";

    public function findAvailableSecretKey(int $length = 10): string
    {
        $unique = false;
        while(!$unique){
            $secret_key = StringFunctions::generateRandomString($length);

            // check to make sure that key doesn't exist for another wishlist in the database
            $checkKey = $this->select("SELECT secret_key FROM $this->table WHERE secret_key = ?", [$secret_key]);

            if(count($checkKey) == 0) $unique = true;
        }
        return $secret_key;
    }

    public function getDuplicateValue(FormField $type, FormField $name, string $username): int
    {
        // find if there is a duplicate type and year in database
        $findDuplicates = $this->select("SELECT id FROM $this->table WHERE type = ? AND wishlist_name = ? AND username = ?", [$type->value, $name->value, $username]);
        return count($findDuplicates);
    }

    public function createWishList(
        FormField $type,
        FormField $name,
        FormField $themeBackgroundID,
        FormField $giftWrapBackgroundID,
        string $username,

    ): bool
    {
        $secret_key = $this->findAvailableSecretKey();
        $duplicateValue = $this->getDuplicateValue($type, $name, $username);

        $year = date('Y');
        $today = date('Y-m-d H:i:s');

        $createWishListQuery = "INSERT INTO $this->table (type, wishlist_name, theme_background_id, theme_gift_wrap_id, year, duplicate, username, secret_key, date_created) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?')";

        $createWishListValues = [
            $type,
            $name,
            $themeBackgroundID,
            $giftWrapBackgroundID,
            $year,
            $duplicateValue,
            $username,
            $secret_key,
            $today
        ];
        
        return $this->write($createWishListQuery, $createWishListValues);
    }

    public function printWishLists(User $user){
        $findWishlists = $this->select("SELECT id, type, wishlist_name, duplicate, theme_background_id, theme_gift_wrap_id FROM wishlists WHERE username = ? ORDER BY date_created DESC", [$user->username]);
        if(count($findWishlists) > 0){
            $theme = new Theme();
            foreach($findWishlists as $row){
                $id = $row["id"];
                $type = $row["type"];
                $list_name = $row["wishlist_name"];
                $duplicate = $row["duplicate"] == 0 ? "" : " ({$row["duplicate"]})";
                $theme_background_id = $row["theme_background_id"];
                $theme_gift_wrap_id = $row["theme_gift_wrap_id"];
                if($theme_background_id != 0){
                    $background_image = $theme->getThemeAttribute($theme_background_id);
                }else{
                    $background_image = "";
                }
                $wrap_image = $theme->getThemeAttribute($theme_gift_wrap_id);
                echo "
                <a class='wishlist-grid-item' href='view-wishlist.php?id=$id'>
                    <div class='items-list preview' style='";
                    echo $background_image == "" ? "" : "background-image: url($this->siteImageFolderWebPath/themes/desktop-thumbnails/$background_image);";
                    echo "'>
                        <div class='item-container'>
                            <img src='$this->siteImageFolderWebPath/themes/gift-wraps/$wrap_image/1.png' class='gift-wrap' alt='gift wrap'>
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
    }
}

?>