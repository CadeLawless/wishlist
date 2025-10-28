<?php

namespace App\Services;

class ItemRenderService
{
    public static function renderItem(array $item, int $wishlistId, int $page, string $type = 'wisher'): string
    {
        $itemName = htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');
        $itemNameShort = htmlspecialchars(substr($item['name'], 0, 25), ENT_QUOTES, 'UTF-8');
        if (strlen($item['name']) > 25) $itemNameShort .= '...';
        
        $price = htmlspecialchars($item['price'], ENT_QUOTES, 'UTF-8');
        $quantity = $item['unlimited'] == 'Yes' ? 'Unlimited' : htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8');
        $notes = htmlspecialchars($item['notes'], ENT_QUOTES, 'UTF-8');
        $notesShort = htmlspecialchars(substr($item['notes'], 0, 30), ENT_QUOTES, 'UTF-8');
        if (strlen($item['notes']) > 30) $notesShort .= '...';
        
        $link = htmlspecialchars($item['link'], ENT_QUOTES, 'UTF-8');
        $imagePath = htmlspecialchars("/wishlist/public/images/item-images/{$wishlistId}/{$item['image']}?t=" . time(), ENT_QUOTES, 'UTF-8');
        $dateAdded = date("n/j/Y g:i A", strtotime($item['date_added']));
        $dateModified = $item['date_modified'] ? date("n/j/Y g:i A", strtotime($item['date_modified'])) : '';
        
        // Get wisher's name for personalized messages
        $wisherName = 'User';
        if ($type === 'buyer') {
            // For buyer view, get the wisher's name from the wishlist
            $wisherName = \App\Models\Wishlist::getWisherName($wishlistId) ?? 'User';
            $wisherName = htmlspecialchars($wisherName, ENT_QUOTES, 'UTF-8');
        } else {
            // For wisher view, get current user's name
            $wisherName = \App\Services\SessionManager::get('name', 'User');
        }
        
        // Priority descriptions with wisher's name
        $priorities = [
            1 => "{$wisherName} absolutely needs this item",
            2 => "{$wisherName} really wants this item", 
            3 => "It would be cool if {$wisherName} had this item",
            4 => "Eh, {$wisherName} could do without this item"
        ];
        
        $priorityText = $priorities[$item['priority']] ?? '';

        // Check if this is a purchased item in buyer view
        $isPurchasedInBuyerView = $type === 'buyer' && $item['purchased'] === 'Yes';

        // Use output buffering to capture the PHP includes
        ob_start();
        ?>
        <div class='item-container'>
            <?php if($isPurchasedInBuyerView): ?>
                <?php
                // Get the wishlist's gift wrap theme
                $wishlist = \App\Models\Wishlist::find($wishlistId);
                $giftWrapImage = null;
                if ($wishlist && $wishlist['theme_gift_wrap_id']) {
                    $giftWrapImage = \App\Services\ThemeService::getGiftWrapImage($wishlist['theme_gift_wrap_id']);
                }
                
                // Use default gift wrap if no theme is set
                if (!$giftWrapImage) {
                    $giftWrapImage = 'default';
                }
                
                // Get a random gift wrap number (1-8) for variety
                $giftWrapNumber = (($item['id'] ?? 1) % 8) + 1;
                ?>
                <img src='/wishlist/public/images/site-images/themes/gift-wraps/<?php echo $giftWrapImage; ?>/<?php echo $giftWrapNumber; ?>.png' class='gift-wrap' alt='gift wrap'>
            <?php endif; ?>
            <div class='item-image-container image-popup-button'>
                <img class='item-image' src='<?php echo $imagePath; ?>' alt='wishlist item image'>
            </div>
            <div class='item-description'>
                <div class='line'><h3><?php echo $itemNameShort; ?></h3></div>
                <?php if(!$isPurchasedInBuyerView): ?>
                    <div class='line'><h4>Price: $<?php echo $price; ?> <span class='price-date'>(as of <?php echo $dateModified ? date("n/j/Y", strtotime($dateModified)) : date("n/j/Y", strtotime($dateAdded)); ?>)</span></h4></div>
                    <div class='line'><h4 class='notes-label'>Quantity Needed:</h4> <?php echo $quantity; ?></div>
                    <div class='line'><h4 class='notes-label'>Notes: </h4><span><?php echo $notesShort; ?></span></div>
                    <div class='line'><h4 class='notes-label'>Priority: </h4><span>(<?php echo $item['priority']; ?>) <?php echo $priorityText; ?></span></div>
                    <div class='icon-options item-options <?php echo $type; ?>-item-options'>
                        <a class='icon-container popup-button' href='#'>
                            <?php require(__DIR__ . '/../../public/images/site-images/icons/view.php'); ?>
                            <div class='inline-label'>View</div>
                        </a>
                        <div class='popup-container hidden'>
                            <div class='popup fullscreen'>
                                <div class='close-container'>
                                    <a href='#' class='close-button'>
                                        <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                    </a>
                                </div>
                                <div class='popup-content'>
                                    <h2 style='margin-top: 0;'>Item Details</h2>
                                    <p><label>Item Name:<br /></label><?php echo $itemName; ?></p>
                                    <p><label>Item Price:<br /></label>$<?php echo $price; ?></p>
                                    <p><label>Website Link:<br /></label><a target='_blank' href='<?php echo $link; ?>'>View on Website</a></p>
                                    <p><label>Notes: </label><br /><?php echo nl2br($notes); ?></p>
                                    <p><label>Priority:<br /></label>(<?php echo $item['priority']; ?>) <?php echo $priorityText; ?></p>
                                    <p><label>Date Added:<br /></label><?php echo $dateAdded; ?></p>
                                    <?php if($dateModified): ?>
                                    <p><label>Last Date Modified:</label><br /><?php echo $dateModified; ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php if($type === 'buyer' && $item['unlimited'] !== 'Yes'): ?>
                            <a class='icon-container popup-button' href='#'>
                                <?php require(__DIR__ . '/../../public/images/site-images/icons/link.php'); ?>
                                <div class='inline-label'>Website Link</div>
                            </a>
                            <div class='popup-container hidden'>
                                <div class='popup'>
                                    <div class='close-container'>
                                        <a href='#' class='close-button'>
                                            <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                        </a>
                                    </div>
                                    <div class='popup-content'>
                                        <h2 style='margin-top: 0;'>Purchase Reminder</h2>
                                        <p>Please make sure to come back and mark this item as purchased if you buy it. <?php echo $wisherName; ?> will not see that you purchased it.</p>
                                        <div style='margin: 16px 0;' class='center'>
                                            <a class='button primary' href='<?php echo $link; ?>' target='_blank'>View Item on Website</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <a class='icon-container' href='<?php echo $link; ?>' target='_blank'>
                                <?php require(__DIR__ . '/../../public/images/site-images/icons/link.php'); ?>
                                <div class='inline-label'>Website Link</div>
                            </a>
                        <?php endif; ?>
                        <?php if($type === 'wisher'): ?>
                            <a class='icon-container' href='/wishlist/<?php echo $wishlistId; ?>/item/<?php echo $item['id']; ?>/edit?pageno=<?php echo $page; ?>'>
                                <?php require(__DIR__ . '/../../public/images/site-images/icons/edit.php'); ?>
                                <div class='inline-label'>Edit</div>
                            </a>
                            <a class='icon-container popup-button' href='#'>
                                <?php require(__DIR__ . '/../../public/images/site-images/icons/delete-x.php'); ?>
                                <div class='inline-label'>Delete</div>
                            </a>
                            <div class='popup-container hidden'>
                                <div class='popup'>
                                    <div class='close-container'>
                                        <a href='#' class='close-button'>
                                            <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                        </a>
                                    </div>
                                    <div class='popup-content'>
                                        <label>Are you sure you want to delete this item?</label>
                                        <p><?php echo $itemName; ?></p>
                                        <div style='margin: 16px 0;' class='center'>
                                            <a class='button secondary no-button' href='#'>No</a>
                                            <form method="POST" action="/wishlist/<?php echo $wishlistId; ?>/item/<?php echo $item['id']; ?>?pageno=<?php echo $page; ?>" style="display: inline;">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class='button primary'>Yes</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                </div>
                <?php if($type === 'buyer'): ?>
                    <?php if($item['purchased'] !== 'Yes'): ?>
                        <div style='margin: 18px 0;' class='center'>
                            <input class='purchased-button popup-button' type='checkbox' id='<?php echo $item['id']; ?>'><label for='<?php echo $item['id']; ?>'> Mark as Purchased</label>
                            <div class='popup-container purchased-popup-<?php echo $item['id']; ?> hidden'>
                                <div class='popup'>
                                    <div class='close-container'>
                                        <a href='#' class='close-button'>
                                            <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                        </a>
                                    </div>
                                    <div class='popup-content'>
                                        <label>Are you sure you want to mark this item as purchased?</label>
                                        <p><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p class='center'><a class='button secondary no-button' href='#'>No</a><a class='button primary purchase-button' href='#' id='purchase-<?php echo $item['id']; ?>'>Yes</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class='center' style="margin: 0.5rem 0;">
                            <h4 class='center'>This item has been purchased!</h4>
                            <span class='unmark-msg'>If you need to unmark an item as purchased, email <a style="font-size: 14px;" href='mailto:support@cadelawless.com'>support@cadelawless.com</a> for help.</span>
                        </div>
                    <?php endif; ?>
            <?php endif; ?>
            <?php if($type === 'wisher'): ?>
                <p class='date-added center'><em>Date Added: <?php echo $dateAdded; ?></em></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
