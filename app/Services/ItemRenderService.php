<?php

namespace App\Services;

class ItemRenderService
{
    public static function renderItem(array $item, int $wishlistId, int $page): string
    {
        $itemName = htmlspecialchars($item['name']);
        $itemNameShort = htmlspecialchars(substr($item['name'], 0, 25));
        if (strlen($item['name']) > 25) $itemNameShort .= '...';
        
        $price = htmlspecialchars($item['price']);
        $quantity = $item['unlimited'] == 'Yes' ? 'Unlimited' : htmlspecialchars($item['quantity']);
        $notes = htmlspecialchars($item['notes']);
        $notesShort = htmlspecialchars(substr($item['notes'], 0, 30));
        if (strlen($item['notes']) > 30) $notesShort .= '...';
        
        $link = htmlspecialchars($item['link']);
        $imagePath = htmlspecialchars("/wishlist/images/item-images/{$wishlistId}/{$item['image']}?t=" . time());
        $dateAdded = date("n/j/Y g:i A", strtotime($item['date_added']));
        $dateModified = $item['date_modified'] ? date("n/j/Y g:i A", strtotime($item['date_modified'])) : '';
        
        // Priority descriptions with user's name
        $userName = $_SESSION['name'] ?? 'User';
        $priorities = [
            1 => "{$userName} absolutely needs this item",
            2 => "{$userName} really wants this item", 
            3 => "It would be cool if {$userName} had this item",
            4 => "Eh, {$userName} could do without this item"
        ];
        
        $priorityText = $priorities[$item['priority']] ?? '';

        // Use output buffering to capture the PHP includes
        ob_start();
        ?>
        <div class='item-container'>
            <div class='item-image-container image-popup-button'>
                <img class='item-image' src='<?php echo $imagePath; ?>' alt='wishlist item image'>
            </div>
            <div class='item-description'>
                <div class='line'><h3><?php echo $itemNameShort; ?></h3></div>
                <div class='line'><h4>Price: $<?php echo $price; ?> <span class='price-date'>(as of <?php echo date("n/j/Y", strtotime($dateAdded)); ?>)</span></h4></div>
                <div class='line'><h4 class='notes-label'>Quantity Needed:</h4> <?php echo $quantity; ?></div>
                <div class='line'><h4 class='notes-label'>Notes: </h4><span><?php echo $notesShort; ?></span></div>
                <div class='line'><h4 class='notes-label'>Priority: </h4><span>(<?php echo $item['priority']; ?>) <?php echo $priorityText; ?></span></div>
                <div class='icon-options item-options wisher-item-options'>
                    <a class='icon-container popup-button' href='#'>
                        <?php require(__DIR__ . '/../../images/site-images/icons/view.php'); ?>
                        <div class='inline-label'>View</div>
                    </a>
                    <div class='popup-container hidden'>
                        <div class='popup fullscreen'>
                            <div class='close-container'>
                                <a href='#' class='close-button'>
                                    <?php require(__DIR__ . '/../../images/site-images/menu-close.php'); ?>
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
                    <a class='icon-container' href='<?php echo $link; ?>' target='_blank'>
                        <?php require(__DIR__ . '/../../images/site-images/icons/link.php'); ?>
                        <div class='inline-label'>Website Link</div>
                    </a>
                    <a class='icon-container' href='/wishlist/<?php echo $wishlistId; ?>/item/<?php echo $item['id']; ?>/edit?pageno=<?php echo $page; ?>'>
                        <?php require(__DIR__ . '/../../images/site-images/icons/edit.php'); ?>
                        <div class='inline-label'>Edit</div>
                    </a>
                    <a class='icon-container popup-button' href='#'>
                        <?php require(__DIR__ . '/../../images/site-images/icons/delete-x.php'); ?>
                        <div class='inline-label'>Delete</div>
                    </a>
                    <div class='popup-container hidden'>
                        <div class='popup'>
                            <div class='close-container'>
                                <a href='#' class='close-button'>
                                    <?php require(__DIR__ . '/../../images/site-images/menu-close.php'); ?>
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
                </div>
                <p class='date-added center'><em>Date Added: <?php echo $dateAdded; ?></em></p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
