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

    $showFeaturePopup = $user['feature_update_seen'] === 'No';

    if ($showFeaturePopup) {
        // Update user to mark feature update as seen
        App\Models\User::updateFeatureUpdateSeen($user['username'], 'Yes');
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

<?php if ($showFeaturePopup): ?>
    <div class="popup-container">
        <div class="popup active">
            <div class="close-container">
                <a href="#" class="close-button">
                    <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                </a>
            </div>
            <div class="popup-content">
                <h2 style="margin-top: 0;">New Feature Update!</h2>
                <p>We've just launched some exciting new features to enhance your Any Wish List experience:</p>
                <ul>
                    <li style="margin-bottom: 0.6rem;"><strong>Profile Customization:</strong> Upload your own profile picture to personalize your account.</li>
                    <li style="margin-bottom: 0.6rem;"><strong>Add Friends Page:</strong> Easily search for friends, view their wish list, and add them to your friend list.</li>
                    <li><strong>Direct Item Addition:</strong> When viewing a friend's wish list, you can now add items directly to your own wish list.</li>
                </ul>
                <p>Hope you enjoy the update — Cade and Meleah :)</p>
            </div>
        </div>
    </div>
<?php endif; ?>

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