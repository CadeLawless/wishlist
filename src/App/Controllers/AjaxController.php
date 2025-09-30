<?php

namespace App\Controllers;

use Core\Controller;
use Helpers\Paginator;
use App\Models\User;
use App\Models\Item;
use App\Models\Theme;

class AjaxController extends Controller
{
    public function changeTheme(): void
    {
        $user = new User();
        $user->changeTheme();
    }

    public function fetchThemeBackgrounds(): void
    {
        $theme = new Theme();
        $theme->getThemeBackgrounds(homeDir: $this->homeDirectory);
    }

    public function fetchThemeBackgroundDropdownOptions(): void
    {
        $theme = new Theme();
        $theme->getThemeBackgroundDropdownOptions();
    }
    public function fetchThemeGiftWrapDropdownOptions(): void
    {
        $theme = new Theme();
        $theme->getThemeGiftWrapDropdownOptions(homeDir: $this->homeDirectory);
    }
    public function fetchPaginatedResults(): void
    {
        $userType = $_SESSION["type"] ?? false;
        if($userType === false){
            echo json_encode(["status" => "error", "message" => "No user type found"]);
            exit;
        }

        $wishlistID = $_SESSION["{$userType}_wishlist_id"] ?? false;
        if($wishlistID === false){
            echo json_encode(["status" => "error", "message" => "No wish list found"]);
            exit;
        }

        $username = $_SESSION["username"] ?? false;

        if(!isset($_POST["page"]) || !isset($_POST["sort_priority"]) || !isset($_POST["sort_price"])){
            echo json_encode(["status" => "error", "message" => "Invalid POST variables"]);
            exit;
        }

        extract($_POST);

        $sortOptions = ["", "1", "2"];

        if(!in_array($sort_priority, $sortOptions)){
            echo json_encode(["status" => "error", "message" => "Sort Priority value is not valid"]);
            exit;
        }

        if(!in_array($sort_price, $sortOptions)){
            echo json_encode(["status" => "error", "message" => "Sort Price value is not valid"]);
            exit;
        }

        $item = new Item($this->homeDirectory);
        $paginationResults = $item->getPaginatedResults(
            type: $userType,
            wishlistID: $wishlistID,
            username: $username,
            sort_priority: $sort_priority,
            sort_price: $sort_price,
            page: $_GET["page"] ?? 1
        );

        echo json_encode($paginationResults);
    }
}

?>