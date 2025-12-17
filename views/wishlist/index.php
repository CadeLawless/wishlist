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

<?php if(isset($all_wishlists) && count($all_wishlists) > 0 && isset($total_pages) && $total_pages > 1): ?>
    <!-- Bottom Pagination controls -->
    <?php 
    $position = 'bottom';
    $total_count = count($all_wishlists);
    $item_label = 'wishlists';
    include __DIR__ . '/../components/pagination-controls.php'; 
    ?>
<?php endif; ?>

<?php if(isset($all_wishlists) && count($all_wishlists) > 0 && isset($total_pages) && $total_pages > 1): ?>
<script src="/public/js/pagination.js?v=2.5"></script>
<?php endif; ?>

<script>
    function scrollToIfNotVisible($elem, padding = 20) {
        const elemTop = $elem.offset().top;
        const elemBottom = elemTop + $elem.outerHeight();

        const navbarHeight = $('.header-container').outerHeight() || 0; 
        const viewportTop = $(window).scrollTop() + navbarHeight;
        const viewportBottom = viewportTop + $(window).height();

        const fullyVisible = elemTop >= viewportTop && elemBottom <= viewportBottom;

        if(!fullyVisible){
            const targetScroll = elemTop - navbarHeight - padding;

            $('html, body').animate({
                scrollTop: targetScroll
            }, 200);
        }
    }

    $(document).ready(function(){
        $(document).on('click', '.dots-icon', function(e){
            e.stopPropagation();
            var quickMenu = $(this).closest('.three-dots-menu').find('.quick-menu');
            $('.quick-menu').not(quickMenu).removeClass('active-menu'); // Hide other open menus
            quickMenu.toggleClass('active-menu');
            if(quickMenu.hasClass('active-menu')){
                setTimeout(() => scrollToIfNotVisible(quickMenu), 200);
            }
        });

        // Hide quick menu when clicking outside
        $(document).on('click', function(e){
            if (!$(e.target).closest('.three-dots-menu').length) {
                $('.quick-menu').removeClass('active-menu');
            }
        });

        $(document).on('click', '.quick-menu-item', function(e){
            e.preventDefault();
            var menuItem = $(this);
            menuItem.addClass('disabled');
            var wishlistId = menuItem.closest('.wishlist-grid-item').data('wishlist-id');
            switch (menuItem.attr('class').split(' ')[1]) {
                case 'toggle-complete':
                    var currentComplete = $(this).data('current-complete');

                    $.ajax({
                        url: `/wishlists/${wishlistId}/toggle-complete`,
                        type: 'POST',
                        data: {},
                        success: function(response) {
                            if (response.status === 'error') {
                                addAlertMessage(response.message);
                                menuItem.removeClass('disabled');
                                return;
                            }
                            menuItem.closest('.three-dots-menu').find('.quick-menu').hide();
                            menuItem.closest('.wishlist-grid-item').fadeOut(500, function() {
                                $(this).remove();
                                $(".paginate-arrow.paginate-first").trigger('click');
                                var newTab = currentComplete === 'Yes' ? 'Active' : 'Inactive';
                                addAlertMessage(`Wish list moved to ${newTab}`);
                                reloadWishLists();
                            });
                        },
                        error: function() {
                            addAlertMessage('Failed to update wish list status');
                            menuItem.removeClass('disabled');
                        }
                    });
                    break;
                case 'toggle-visibility':
                    var currentVisibility = $(this).data('current-visibility');

                    $.ajax({
                        url: `/wishlists/${wishlistId}/toggle-visibility`,
                        type: 'POST',
                        data: {},
                        success: function(response) {
                            if (response.status === 'error') {
                                addAlertMessage(response.message);
                                menuItem.removeClass('disabled');
                                return;
                            }
                            var newVisibility = currentVisibility === 'Public' ? 'Hidden' : 'Public';
                            // Update the icon and text in the quick menu
                            if(newVisibility === 'Public'){
                                menuItem.find('.menu-icon').html(`<?php ob_start(); require(__DIR__ . '/../../public/images/site-images/icons/hide-view.php'); echo addslashes(ob_get_clean()); ?>`);
                                menuItem.contents().filter(function() { return this.nodeType === 3; }).last().replaceWith(' Hide');
                            }else{
                                menuItem.find('.menu-icon').html(`<?php ob_start(); require(__DIR__ . '/../../public/images/site-images/icons/view.php'); echo addslashes(ob_get_clean()); ?>`);
                                menuItem.contents().filter(function() { return this.nodeType === 3; }).last().replaceWith(' Make Public');
                            }
                            // Update the private icon visibility
                            var wishlistItem = menuItem.closest('.wishlist-grid-item');
                            if(newVisibility === 'Public'){
                                wishlistItem.find('.private-wishlist-icon').remove();
                                wishlistItem.find('.items-list').removeClass('private');
                            }else{
                                var privateIconHtml = `<?php ob_start(); require(__DIR__ . '/../../public/images/site-images/icons/hide-view.php'); echo addslashes(ob_get_clean()); ?>`;
                                wishlistItem.prepend(`<div class='private-wishlist-icon'>${privateIconHtml}</div>`);
                                wishlistItem.find('.items-list').addClass('private');
                            }
                            var alertMessage = newVisibility === 'Public' ? 'Wish list is now public' : 'Wish list is now hidden';
                            addAlertMessage(alertMessage);
                            menuItem.removeClass('disabled').data('current-visibility', newVisibility);
                        },
                        error: function() {
                            addAlertMessage('Failed to update wish list visibility');
                            menuItem.removeClass('disabled');
                        }
                    });
                    break;
            }

            menuItem.closest('.quick-menu').removeClass('active-menu');
        });

        function addAlertMessage(message) {
            $(".alert-message").remove(); // Remove existing messages
            const alertPopup = $(`
                <div class="alert-message">
                    <p style="margin: 0;">${message}</p>
                </div>
            `);
            $('body').append(alertPopup);
            $('.alert-message').fadeOut(5000, function() {
                $(this).remove();
            });
        }

        function reloadWishLists() {
            const currentUrl = window.location.href;
            $.ajax({
                url: currentUrl + "/reload",
                type: 'GET',
                success: function(response) {
                    $('.wishlist-grid').html(response.html);
                    if(response.count <= 12){
                        $('.paginate-container').remove();
                    }
                },
                error: function() {
                    addAlertMessage('Failed to reload wish lists');
                }
            });
        }
    });
</script>