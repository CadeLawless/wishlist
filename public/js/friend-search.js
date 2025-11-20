$(document).ready(function() {
    $('#friends-search').focus();

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