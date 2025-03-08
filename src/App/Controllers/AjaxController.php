<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\User;
use App\Models\WishList;
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
}

?>