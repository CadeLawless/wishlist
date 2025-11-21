<?php
$currentPage = isset($_SERVER["REQUEST_URI"]) ? explode("?", $_SERVER["REQUEST_URI"])[0] : "/";

// Check cookie
$showPopup = false;

if(isset($user)) {
    $pendingInvitationsCount = App\Models\FriendInvitation::getPendingInvitationsCount($user['username']);

    if ($pendingInvitationsCount > 0) {
        if($currentPage === "/add-friends") {
            $showPopup = false;
            // Set cookie for 24 hours
            setcookie('last_invitation_popup', time(), time() + 86400, '/');
        } else {
            if (!isset($_COOKIE['last_invitation_popup'])) {
                $showPopup = true;
            } else {
                $lastShown = (int) $_COOKIE['last_invitation_popup'];
                if (time() - $lastShown >= 86400) { // 24 hours
                    $showPopup = true;
                }
            }
        }
    }else {
        // No pending invitations, ensure cookie is cleared
        if (isset($_COOKIE['last_invitation_popup'])) {
            setcookie('last_invitation_popup', '', time() - 3600, '/');
        }
    }
}
?>
<div class="header-container">
    <div class="header">
        <div class="title">
            <a class="nav-title" href="/"><?php require(__DIR__ . "/../../public/images/site-images/logo.php"); ?></a>
            <a href="#" class="dark-mode-link"><?php require(__DIR__ . "/../../public/images/site-images/icons/dark-mode.php"); ?></a>
            <a href="#" class="light-mode-link"><?php require(__DIR__ . "/../../public/images/site-images/icons/light-mode.php"); ?></a>
        </div>
        <div class="menu">
            <?php
            require(__DIR__ . "/../../public/images/site-images/hamburger-menu.php");
            require(__DIR__ . "/../../public/images/site-images/menu-close.php");
            ?>
            <div class="menu-links">
                <a class="nav-link<?php if($currentPage == "/" || $currentPage == "/wishlist") echo " active"; ?>" href="/">Home<div class="underline"></div></a>
                <a class="nav-link<?php if($currentPage == "/wishlists/create") echo " active"; ?>" href="/wishlists/create">Create Wishlist<div class="underline"></div></a>
                <a class="nav-link<?php if($currentPage == "/wishlists") echo " active"; ?>" href="/wishlists">View Wishlists<div class="underline"></div></a>
                <a class="nav-link<?php if($currentPage == "/add-friends") echo " active"; ?>" href="/add-friends">Add Friends<div class="underline"></div></a>
                <div class="nav-link dropdown-link profile-link<?php if(in_array($currentPage, ["/profile", "/admin"])) echo " active-page"; ?>">
                    <div class="outer-link">
                        <span class="profile-icon"><?php require(__DIR__ . "/../../public/images/site-images/profile-icon.php"); ?></span>
                        <span>My Account</span>
                        <span class="dropdown-arrow"><?php require(__DIR__ . "/../../public/images/site-images/dropdown-arrow.php"); ?></span>
                    </div>
                    <div class="underline"></div>
                    <div class="dropdown-menu hidden">
                        <a class="dropdown-menu-link" href="/profile">View Profile</a>
                        <?php if(isset($user['role']) && $user['role'] == 'Admin'){ ?>
                            <a class="dropdown-menu-link" href="/admin">Admin Center</a>
                        <?php } ?>
                        <a class="dropdown-menu-link" href="/logout">Log Out</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($showPopup): ?>
    <div id="invitation-popup">
        <p style="margin-top: 0;">Other wishers have added you as their friend!</p>
        <div style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: end; align-items: center;">
            <a href="/add-friends" class="button primary">Go to Friends</a>
            <button class="button secondary invitation-close-button">Dismiss</button>
        </div>
    </div>

    <script>
    document.querySelector('.invitation-close-button').addEventListener('click', function() {
        // Set cookie for 24 hours
        const now = Math.floor(Date.now() / 1000);
        document.cookie = "last_invitation_popup=" + now + "; max-age=86400; path=/";

        this.closest('#invitation-popup').remove();
    });
    </script>
<?php endif; ?>