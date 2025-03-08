<?php

namespace App\Models;

use Core\Model;
use Helpers\FormField;
use App\Models\User;
use Helpers\StringFunctions;

class WishList extends Model
{
    protected string $table = 'wishlists';

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
}

?>