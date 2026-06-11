<?php
// Display flash messages
if (isset($flash['success'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../public/images/site-images/menu-close.php');
                echo "</a>
            </div>
            <div class='popup-content'>
                <p><label>" . htmlspecialchars($flash['success']) . "</label></p>
            </div>
        </div>
    </div>";
}

if (isset($flash['error'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../public/images/site-images/menu-close.php');
                echo "</a>
            </div>
            <div class='popup-content'>
                <p><label>" . htmlspecialchars($flash['error']) . "</label></p>
            </div>
        </div>
    </div>";
}
?>

<h1 class="center"><?= htmlspecialchars($title); ?></h1>
<p class="center" style="margin: 0 0 36px;"><a class="button primary" href="/wishlists/create">Create a New Wish List</a></p>

<div class="wishlist-tabs">
    <a class="wishlist-tab<?php if($active) echo ' active'; ?>" href="/wishlists">Active</a>
    <a class="wishlist-tab<?php if(!$active) echo ' active'; ?>" href="/wishlists/inactive">Inactive</a>
</div>

<?php if(isset($all_wishlists) && count($all_wishlists) > 0 && isset($total_pages) && $total_pages > 1): ?>
    <!-- Top Pagination controls -->
    <?php 
    $position = 'top';
    $backgroundColorClass = 'no-background';
    include __DIR__ . '/../components/pagination-controls.php'; 
    ?>
<?php endif; ?>

<div class="wishlist-grid">
    <?php
    if(count($wishlists) > 0){
        // Use WishlistRenderService to generate the HTML
        echo \App\Services\WishlistRenderService::generateWishlistsHtml($wishlists);
    }else{
        echo "<p style='grid-column: 1 / -1;' class='center'>It doesn't look like you have any " .  ($active ? "active" : "inactive") . " wish lists right now</p>";
    }
    ?>
</div>

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
<?php if(isset($all_wishlists) && count($all_wishlists) > 0 && isset($total_pages) && $total_pages > 1): ?>
    <!-- Bottom Pagination controls -->
    <?php 
    $position = 'bottom';
    $total_count = count($all_wishlists);
    $item_label = 'wishlists';
    $backgroundColorClass = 'no-background';
    include __DIR__ . '/../components/pagination-controls.php'; 
    ?>
<?php endif; ?>

<?php require __DIR__ . '/../components/wishlist-action-popups.php'; ?>

<?php if(isset($all_wishlists) && count($all_wishlists) > 0 && isset($total_pages) && $total_pages > 1): ?>
<script src="/public/js/pagination.js?v=2.5"></script>
<?php endif; ?>

<script src="/public/js/form-validation.js?v=2.5"></script>
<script src="/public/js/add-alert-message.js"></script>
<?php require __DIR__ . '/../../public/js/wishlist-action-menu.php'; ?>

<script>
    $(document).ready(function(){
        $(document).on('click', '.wishlist-checkbox', function(e){
            e.preventDefault();
            var checkbox = $(this);
            var wishlistId = checkbox.closest('.wishlist-grid-item').data('wishlist-id');
            checkbox.toggleClass('checked');
            if(checkbox.hasClass('checked')){
                checkbox.closest('.wishlist-grid-item').addClass('selected');
                checkbox.closest('.wishlist-grid-item').find('.click-to-select').text('Selected');
            }else{
                checkbox.closest('.wishlist-grid-item').removeClass('selected');
                checkbox.closest('.wishlist-grid-item').find('.click-to-select').text('Click to Select');
            }
            showBulkActionsBarIfWishListsSelected();
            $(document.body).click();
        });

        $(document).on('click', '.wishlist-grid-item.bulk-select .items-list, .wishlist-grid-item.bulk-select .private-wishlist-icon, .wishlist-grid-item.bulk-select .wishlist-grid-item-footer, .wishlist-grid-item.bulk-select .click-to-select', function(){
            var selectedIds = getSelectedWishlists();
            if(selectedIds.length > 0){
                $(this).closest('.wishlist-grid-item').find('.wishlist-checkbox').click();
            }
        });

        $('#select-all-button').on('click', function(e){
            e.preventDefault();
            selectAllWishlists();
            showBulkActionsBarIfWishListsSelected();
        });

        $('#clear-selection-button').on('click', function(e){
            e.preventDefault();
            clearAllSelections();
            showBulkActionsBarIfWishListsSelected();
        });

        var bulkActionDropdownFiltered = false;

        var originalContainerPaddingBottom = null;

        function showBulkActionsBarIfWishListsSelected() {
            if (!bulkActionDropdownFiltered) {
                var actionItems = $('#bulk-action-dropdown-menu .bulk-action-item');
                var isActiveTab = $('.wishlist-tab.active').text().trim() === 'Active';
                actionItems.show();
                if(isActiveTab){
                    // Active tab - hide Activate option
                    actionItems.filter('#bulk-reactivate-wishlists').hide();
                }else{
                    // Inactive tab - hide Deactivate option
                    actionItems.filter('#bulk-deactivate-wishlists').hide();
                }
                bulkActionDropdownFiltered = true;
            }

            // check if all selected wish lists are public or hidden to adjust visibility options
            var allPublic = true;
            var allHidden = true;
            $('.wishlist-checkbox.checked').each(function(){
                var visibility = $(this).closest('.wishlist-grid-item').data('visibility');
                if(visibility === 'Public'){
                    allHidden = false;
                }else{
                    allPublic = false;
                }
            });
            var actionItems = $('#bulk-action-dropdown-menu .bulk-action-item');
            if(allPublic){
                // All selected are public - hide Make Public option
                actionItems.filter('#bulk-make-public-wishlists').hide();
                actionItems.filter('#bulk-hide-wishlists').show();
            }else if(allHidden){
                // All selected are hidden - hide Make Hidden option
                actionItems.filter('#bulk-hide-wishlists').hide();
                actionItems.filter('#bulk-make-public-wishlists').show();
            }else{
                // Mixed - show both options
                actionItems.filter('#bulk-make-public-wishlists').show();
                actionItems.filter('#bulk-hide-wishlists').show();
            }

            var selectedIds = getSelectedWishlists();
            if(selectedIds.length > 0){
                $('#bulk-actions-bar').addClass('active');
                $('.wishlist-grid-item').addClass('bulk-select');
                $('#selected-count').text(selectedIds.length);
                $("footer").hide();
                
                // Store original padding only once, before changing it
                if (originalContainerPaddingBottom === null) {
                    originalContainerPaddingBottom = parseInt($("#container").css("padding-bottom")) || 0;
                    // hide alert message when selecting/deselecting wish lists to avoid confusion with bulk action messages
                    $('.alert-message').remove();
                }

                $("#container").css("padding-bottom", $("#bulk-actions-bar").outerHeight() + "px");
            }else{
                $('#bulk-actions-bar').removeClass('active');
                $('.wishlist-grid-item').removeClass('bulk-select');
                $('#selected-count').text('0');
                $("footer").show();
                
                // Restore original padding
                if (originalContainerPaddingBottom !== null) {
                    $("#container").css("padding-bottom", originalContainerPaddingBottom + "px");
                    originalContainerPaddingBottom = null;
                }

                $('#bulk-action-dropdown-button .action-text').text('Bulk Actions');
            }
        }

        function getSelectedWishlists() {
            var selectedIds = [];
            $('.wishlist-checkbox.checked').each(function(){
                var wishlistId = $(this).closest('.wishlist-grid-item').data('wishlist-id');
                selectedIds.push(wishlistId);
            });
            return selectedIds;
        }

        function selectAllWishlists() {
            $('.wishlist-checkbox').each(function(){
                $(this).removeClass('checked');
                $(this).click();
            });
        }

        function clearAllSelections() {
            $('.wishlist-checkbox').each(function(){
                $(this).addClass('checked');
                $(this).click();
            });
        }

        $(document).on('click', '#bulk-action-dropdown-button', function(e){
            e.preventDefault();
            $('#bulk-action-dropdown-menu').toggleClass('active-menu');
        });

        $(document).on('click', function(e){
            if (!$(e.target).closest('#bulk-action-dropdown-menu').length && !$(e.target).closest('#bulk-action-dropdown-button').length) {
                $('#bulk-action-dropdown-menu').removeClass('active-menu');
            }
        });

        $(document).on('click', '.bulk-action-item', function(e){
            e.preventDefault();
            var actionItem = $(this);
            var actionText = actionItem.text().trim();
            $('#bulk-action-dropdown-button .action-text').text(actionText);
            $('#bulk-action-dropdown-menu').removeClass('active-menu');
            actionItem.addClass('selected');
            actionItem.siblings().removeClass('selected');
            $('#bulk-action-confirm-button').attr('data-selected-action', actionItem.data('action'));
        });

        $(document).on('click', '#bulk-action-confirm-button', function(e){
            e.preventDefault();
            $(this).prop('disabled', true).html('<div class="loading-spinner"></div>');
            var selectedAction = $(this).attr('data-selected-action');
            if(!selectedAction){
                // Shake bulk actions dropdown button to indicate error
                var dropdownButton = $('#bulk-action-dropdown-button');
                dropdownButton.removeClass('shake');
                // force reflow
                dropdownButton[0].offsetWidth;
                dropdownButton.addClass('shake');
                $(this).prop('disabled', false).html('Confirm');
                return;
            }
            var selectedIds = getSelectedWishlists();
            if(!selectedAction){
                addAlertMessage('Please select a bulk action');
                $(this).prop('disabled', false).html('Confirm');
                return;
            }
            if(selectedIds.length === 0){
                addAlertMessage('Please select at least one wish list');
                $(this).prop('disabled', false).html('Confirm');
                return;
            }
            $.ajax({
                url: '/wishlists/bulk-action',
                type: 'POST',
                data: { action: selectedAction, ids: selectedIds },
                success: function(response) {
                    if (response.status === 'error') {
                        $('#bulk-action-confirm-button').prop('disabled', false).html('Confirm');
                        addAlertMessage(response.message);
                        return;
                    }
                    addAlertMessage(response.message);
                    $('.bulk-action-item').removeClass('selected');
                    clearAllSelections();
                    showBulkActionsBarIfWishListsSelected();
                    reloadWishLists();
                    $('#bulk-action-confirm-button').prop('disabled', false).html('Confirm');
                },
                error: function() {
                    $('#bulk-action-confirm-button').prop('disabled', false).html('Confirm');
                    addAlertMessage('Failed to perform bulk action');
                }
            });
        });

        function reloadWishLists() {
            const params = new URLSearchParams(window.location.search);
            const pageno = params.get('pageno');
            $.ajax({
                url: window.location.pathname + "/reload?pageno=" + pageno,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('.wishlist-grid').html(response.html);
                    if(response.count <= 12){
                        $('.paginate-container').remove();
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Status:', status);
                    console.log('Error:', error);
                    console.log('Response:', xhr.responseText);
                    addAlertMessage('Failed to reload wish lists');
                }
            });
        }
    });
</script>