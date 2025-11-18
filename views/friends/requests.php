
<?php if(count($receivedFriendRequests) === 0): ?>
    <div class="center">
        <p>You don't have any pending friend requests.</p>
    </div>
<?php else: ?>
    <p>
        <a class="button accent" href="/add-friends">
            Back to Add Friends
        </a>
    </p>
    <div class="friends-section">
        <?php if(count($receivedFriendRequests) > 0): ?>
            <h2>Friend Requests</h2>
            <div class="friends-search-results-container">
                <?php echo \App\Services\FriendRenderService::generateUserSearchResults($receivedFriendRequests, 'received'); ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script src="/public/js/button-loading.js"></script>
<script>
    $(document).ready(function() {
        $(document).on('click', '.accept-button', function(e) {
            e.preventDefault();
            const button = $(this);
            showButtonLoading(button);
            button.closest('.user-result').find('.decline-button').addClass('disabled');
            const targetUsername = button.data('username');

            $.ajax({
                url: '/add-friends/accept',
                method: 'POST',
                data: { target_username: targetUsername },
                success: function(response) {
                    if (response.status === 'success') {
                        hideButtonLoading(button);
                        keepButtonSize(button);
                        button.text('Added!').addClass('disabled');
                        button.closest('.user-result').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        hideButtonLoading(button);
                        showButtonFailed(button);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Accept request error:', error);
                    hideButtonLoading(button);
                    showButtonFailed(button);
                }
            });
        });

        $(document).on('click', '.decline-button', function(e) {
            e.preventDefault();
            const button = $(this);
            showButtonLoading(button);
            button.closest('.user-result').find('.accept-button').addClass('disabled');
            const targetUsername = button.data('username');

            $.ajax({
                url: '/add-friends/decline',
                method: 'POST',
                data: { target_username: targetUsername },
                success: function(response) {
                    if (response.status === 'success') {
                        hideButtonLoading(button);
                        button.text('Removed!').addClass('disabled');
                        button.closest('.user-result').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        hideButtonLoading(button);
                        showButtonFailed(button);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Decline request error:', error);
                    hideButtonLoading(button);
                    showButtonFailed(button);
                }
            });
        });
    });
</script>