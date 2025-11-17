<?php 
if(isset($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
    $allUsers = \App\Services\FriendService::searchForRequests($user['username'], $searchTerm);
}
?>

<div class="center">
    <h1>Add friends to see what others are wishing for.</h1>
    <?php
    $options = [
        'placeholder' => 'Search for people by name or username...',
        'input_id' => 'friends-search',
        'input_class' => 'friends-search-input',
        'container_class' => 'center',
        'search_term' => $searchTerm ?? ''
    ];
    include __DIR__ . '/../components/admin-table-search.php';
    ?>
</div>

<div class="friends-search-results-container">
    <?php if (isset($searchTerm) && $searchTerm !== ''): ?>
        <?php echo \App\Services\FriendRenderService::generateUserSearchResults($allUsers ?? []); ?>
    <?php endif; ?>
</div>

<script src="/public/js/admin-table-search.js"></script>

<script>
    $(document).ready(function() {
        function showButtonLoading(button) {
            keepButtonSize(button);
            button.addClass('loading');
        }

        function hideButtonLoading(button) {
            button.css({
                'width': '',
                'height': ''
            });
            button.removeClass('loading');
        }

        function keepButtonSize(button) {
            button.css({
                'width': button.outerWidth() + 'px',
                'height': button.outerHeight() + 'px'
            });
        }

        function showButtonFailed(button) {
            keepButtonSize(button);
            button.text('Failed to Send').addClass('disabled failed');
            setTimeout(() => {
                button.text('Send Friend Request').removeClass('disabled failed');
                button.css({
                    'width': '',
                    'height': ''
                });
            }, 5000);
        }

        $(document).on('click', '.add-friend-button', function(e) {
            e.preventDefault();
            const button = $(this);
            showButtonLoading(button);
            const targetUsername = button.data('username');

            $.ajax({
                url: '/add-friends/send-request',
                method: 'POST',
                data: { target_username: targetUsername },
                success: function(response) {
                    if (response.status === 'success') {
                        hideButtonLoading(button);
                        keepButtonSize(button);
                        button.text('Sent!').addClass('disabled');
                    } else {
                        hideButtonLoading(button);
                        showButtonFailed(button);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Friend request error:', error);
                    hideButtonLoading(button);
                    showButtonFailed(button);
                }
            });
        });
    });
</script>