<?php

namespace App\Services;

class AdminRenderService
{
    /**
     * Generate HTML for admin users table rows
     */
    public static function generateUsersTableHtml(array $users): string
    {
        ob_start();
        foreach ($users as $userRow) {
            $name = htmlspecialchars($userRow['name']);
            $username = htmlspecialchars($userRow['username']);
            $email = $userRow['email'] ?? '';
            $role = htmlspecialchars($userRow['role']);
            ?>
            <tr>
                <td data-label="Name"><?php echo $name; ?></td>
                <td data-label="Username"><?php echo $username; ?></td>
                <td data-label="Email">
                    <?php if(empty($email)): ?>
                        Not set up yet
                    <?php else: ?>
                        <a href="mailto:<?php echo htmlspecialchars($email); ?>"><?php echo htmlspecialchars($email); ?></a>
                    <?php endif; ?>
                </td>
                <td data-label="Role"><?php echo $role; ?></td>
                <td>
                    <div class="icon-group">
                        <a class="icon-container" href="/admin/wishlists?username=<?php echo urlencode($username); ?>">
                            <?php require(__DIR__ . '/../../public/images/site-images/icons/wishlist.php'); ?>
                        </a>
                        <a class="icon-container" href="/admin/users/edit?username=<?php echo urlencode($username); ?>">
                            <?php require(__DIR__ . '/../../public/images/site-images/icons/edit.php'); ?>
                        </a>
                    </div>
                </td>
            </tr>
            <?php
        }
        return ob_get_clean();
    }

    /**
     * Generate HTML for admin backgrounds table rows
     */
    public static function generateBackgroundsTableHtml(array $backgrounds, int $currentPage): string
    {
        ob_start();
        foreach ($backgrounds as $background) {
            $id = htmlspecialchars($background['theme_id']);
            $tag = htmlspecialchars($background['theme_tag'] ?? '');
            $name = htmlspecialchars($background['theme_name']);
            $image = htmlspecialchars($background['theme_image']);
            $defaultGiftWrap = htmlspecialchars($background['default_gift_wrap'] ?? '');
            
            // Build thumbnail path
            $thumbnailPath = '/public/images/site-images/themes/desktop-thumbnails/' . $image;
            $thumbnailExists = file_exists(__DIR__ . '/../../public/images/site-images/themes/desktop-thumbnails/' . $image);
            ?>
            <tr>
                <td data-label="ID"><?php echo $id; ?></td>
                <td data-label="Tag"><?php echo $tag; ?></td>
                <td data-label="Name"><?php echo $name; ?></td>
                <td data-label="Thumbnail">
                    <?php if ($thumbnailExists): ?>
                        <img src="<?php echo $thumbnailPath; ?>" alt="<?php echo $name; ?>" style="max-width: 80px; max-height: 80px; border-radius: 4px; object-fit: cover;">
                    <?php else: ?>
                        <span style="color: var(--text-secondary);">No thumbnail</span>
                    <?php endif; ?>
                </td>
                <td data-label="Image"><?php echo $image; ?></td>
                <td data-label="Default Gift Wrap"><?php echo $defaultGiftWrap; ?></td>
                <td>
                    <div class="icon-group">
                        <a class="icon-container" href="/admin/backgrounds/edit?id=<?php echo $id; ?>&pageno=<?php echo $currentPage; ?>">
                            <?php require(__DIR__ . '/../../public/images/site-images/icons/edit.php'); ?>
                        </a>
                        <a class="icon-container popup-button" href="#">
                            <?php require(__DIR__ . '/../../public/images/site-images/icons/delete-trashcan.php'); ?>
                        </a>
                        <div class='popup-container hidden'>
                            <div class='popup'>
                                <div class='close-container'>
                                    <a href='#' class='close-button'>
                                        <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                    </a>
                                </div>
                                <div class='popup-content'>
                                    <label>Are you sure you want to delete this background?</label>
                                    <p><?php echo $name; ?></p>
                                    <div style='margin: 16px 0;' class='center'>
                                        <a class='button secondary no-button' href='#'>No</a>
                                        <a class='button primary' href='/admin/backgrounds/delete?id=<?php echo $id; ?>&pageno=<?php echo $currentPage; ?>'>Yes</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            <?php
        }
        return ob_get_clean();
    }

    /**
     * Generate HTML for admin gift wraps table rows
     */
    public static function generateGiftWrapsTableHtml(array $giftWraps, int $currentPage): string
    {
        ob_start();
        foreach ($giftWraps as $giftWrap) {
            $id = htmlspecialchars($giftWrap['theme_id']);
            $tag = htmlspecialchars($giftWrap['theme_tag'] ?? '');
            $name = htmlspecialchars($giftWrap['theme_name']);
            $image = htmlspecialchars($giftWrap['theme_image']);
            
            // Build first gift wrap image path (1.png)
            $firstWrapPath = '/public/images/site-images/themes/gift-wraps/' . $image . '/1.png';
            $firstWrapExists = file_exists(__DIR__ . '/../../public/images/site-images/themes/gift-wraps/' . $image . '/1.png');
            ?>
            <tr>
                <td data-label="ID"><?php echo $id; ?></td>
                <td data-label="Tag"><?php echo $tag; ?></td>
                <td data-label="Name"><?php echo $name; ?></td>
                <td data-label="Preview">
                    <?php if ($firstWrapExists): ?>
                        <img src="<?php echo $firstWrapPath; ?>" alt="<?php echo $name; ?>" style="max-width: 80px; max-height: 80px; border-radius: 4px; object-fit: cover;">
                    <?php else: ?>
                        <span style="color: var(--text-secondary);">No preview</span>
                    <?php endif; ?>
                </td>
                <td data-label="Folder"><?php echo $image; ?></td>
                <td>
                    <div class="icon-group">
                        <a class="icon-container" href="/admin/gift-wraps/edit?id=<?php echo $id; ?>&pageno=<?php echo $currentPage; ?>">
                            <?php require(__DIR__ . '/../../public/images/site-images/icons/edit.php'); ?>
                        </a>
                        <a class="icon-container popup-button" href="#">
                            <?php require(__DIR__ . '/../../public/images/site-images/icons/delete-trashcan.php'); ?>
                        </a>
                        <div class='popup-container hidden'>
                            <div class='popup'>
                                <div class='close-container'>
                                    <a href='#' class='close-button'>
                                        <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                    </a>
                                </div>
                                <div class='popup-content'>
                                    <label>Are you sure you want to delete this gift wrap?</label>
                                    <p><?php echo $name; ?></p>
                                    <div style='margin: 16px 0;' class='center'>
                                        <a class='button secondary no-button' href='#'>No</a>
                                        <a class='button primary' href='/admin/gift-wraps/delete?id=<?php echo $id; ?>&pageno=<?php echo $currentPage; ?>'>Yes</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            <?php
        }
        return ob_get_clean();
    }

    /**
     * Generate HTML for admin wishlists table rows
     */
    public static function generateWishlistsTableHtml(array $wishlists, int $currentPage): string
    {
        ob_start();
        foreach ($wishlists as $wishlist) {
            $id = htmlspecialchars($wishlist['id']);
            $wishlistName = htmlspecialchars($wishlist['wishlist_name']);
            $userName = htmlspecialchars($wishlist['user_name'] ?? '');
            $username = htmlspecialchars($wishlist['username'] ?? '');
            $secretKey = htmlspecialchars($wishlist['secret_key'] ?? '');
            $type = htmlspecialchars($wishlist['type'] ?? '');
            $dateCreated = htmlspecialchars($wishlist['date_created'] ?? '');
            ?>
            <tr>
                <td data-label="ID"><?php echo $id; ?></td>
                <td data-label="Name"><?php echo $wishlistName; ?></td>
                <td data-label="User"><?php echo $userName; ?> (<?php echo $username; ?>)</td>
                <td data-label="Type"><?php echo $type; ?></td>
                <td data-label="Secret Key"><?php echo $secretKey; ?></td>
                <td data-label="Date Created"><?php echo $dateCreated; ?></td>
                <td>
                    <div class="icon-group">
                        <a class="icon-container" href="/buyer/<?php echo $secretKey; ?>" target="_blank" title="View as buyer">
                            <?php require(__DIR__ . '/../../public/images/site-images/icons/wishlist.php'); ?>
                        </a>
                        <a class="icon-container" href="/admin/wishlists/view?id=<?php echo $id; ?>" title="View as admin">
                            <?php require(__DIR__ . '/../../public/images/site-images/icons/edit.php'); ?>
                        </a>
                    </div>
                </td>
            </tr>
            <?php
        }
        return ob_get_clean();
    }
}

