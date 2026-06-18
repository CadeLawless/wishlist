<div id="bulk-actions-bar">
    <div class="selection-actions-buttons">
        <button id="select-all-button" class="button secondary">Select All</button>
        <button id="clear-selection-button" class="button secondary">Clear Selection</button>
    </div>
    <div class="bulk-actions-container">
        <div id="selected-count-container"><span id="selected-count">0</span> wish list(s) selected</div>
        <div class="bulk-button-group">
            <div class="action-dropdown-container">
                <button id="bulk-action-dropdown-button" class="button dropdown">
                    <span class="action-text">Bulk Actions</span>
                    <span>▼</span>
                </button>
                <div id="bulk-action-dropdown-menu">
                    <button class="bulk-action-item" data-action="deactivate" id="bulk-deactivate-wishlists">
                        <span class="menu-icon"><?php require(__DIR__ . '/../../public/images/site-images/icons/cancel.php'); ?></span>
                        <span>Deactivate</span>
                    </button>
                    <button class="bulk-action-item" data-action="reactivate" id="bulk-reactivate-wishlists">
                        <span class="menu-icon"><?php require(__DIR__ . '/../../public/images/site-images/icons/checkmark.php'); ?></span>
                        <span>Reactivate</span>
                    </button>
                    <button class="bulk-action-item" data-action="make-public" id="bulk-make-public-wishlists">
                        <span class="menu-icon"><?php require(__DIR__ . '/../../public/images/site-images/icons/view.php'); ?></span>
                        <span>Make Public</span>
                    </button>
                    <button class="bulk-action-item" data-action="hide" id="bulk-hide-wishlists">
                        <span class="menu-icon"><?php require(__DIR__ . '/../../public/images/site-images/icons/hide-view.php'); ?></span>
                        <span>Hide</span>
                    </button>
                    <button class="bulk-action-item" data-action="delete" id="bulk-delete-wishlists">
                        <span class="menu-icon"><?php require(__DIR__ . '/../../public/images/site-images/icons/delete-trashcan.php'); ?></span>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
            <button id="bulk-action-confirm-button" class="button primary">Confirm</button>
        </div>
    </div>
</div>