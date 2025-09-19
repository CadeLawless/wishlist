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

        $sorts = [];

        if($sort_priority != ""){
            $sorts[] = "priority " . ($sort_priority == "1" ? "ASC" : "DESC");
        }

        if($sort_price != ""){
            $sorts[] = "priority " . ($sort_price == "1" ? "ASC" : "DESC");
        }

        list($sql, $params) = Item::buildBaseQuery(wishlistID: $wishlistID, username: $username, sorts: $sorts);

        $paginator = (new Paginator($sql, $params))
            ->setLimit(12)
            ->setPage($_GET["page"]);

        $data = $paginator->getData();
        $info = $paginator->getPaginationInfo();

        $item = new Item($this->homeDirectory);
        ob_start();
        $item->writeItemsGrid(
            items: $data,
            type: "wisher"
        );
        $results = ob_get_clean();

        echo json_encode([
            "status" => "success",
            "message" => "",
            "html" => $results,
            "current" => $info["current"],
            "total" => $info["totalRows"],
            "paginationInfo" => "{$info['start']}-{$info['end']} of {$info['totalRows']}",
            "filters" => ""
        ]);
    }
}

?>