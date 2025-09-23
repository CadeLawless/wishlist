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
        $item = new Item($this->homeDirectory);
        $item->getPaginatedResults();
    }
}

?>