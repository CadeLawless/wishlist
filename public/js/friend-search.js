$(document).ready(function() {
    if(window.location.pathname === '/friends/find'){
        $('#friends-search').focus();
        // Make sure cursor is at the end of the input
        const val = $('#friends-search').val();
        $('#friends-search').val('');
        $('#friends-search').val(val);
    }

    $(document).on('click', '.add-friend-button', function(e) {
        e.preventDefault();
        const button = $(this);
        showButtonLoading(button);
        const targetUsername = button.data('username');

        $.ajax({
            url: '/friends/add',
            method: 'POST',
            data: { target_username: targetUsername },
            success: function(response) {
                if (response.status === 'success') {
                    hideButtonLoading(button);
                    keepButtonSize(button);
                    button.text('Added!').addClass('disabled');
                    if(button.closest('.user-result').find('.decline-button').length){
                        button.closest('.user-result').find('.decline-button').addClass('disabled');
                    }
                    button.closest('.user-result').fadeOut(1000, function() {
                        $(this).remove();
                    });
                    setTimeout(() => {
                        $('#friends-search').trigger('input');
                    }, 700);
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

    $(document).on('click', '.remove-button', function(e) {
        e.preventDefault();
        const button = $(this);
        const targetUsername = button.data('username');
        showButtonLoading(button);

        $.ajax({
            url: '/friends/remove',
            method: 'POST',
            data: { target_username: targetUsername },
            success: function(response) {
                if (response.status === 'success') {
                    hideButtonLoading(button);
                    keepButtonSize(button);
                    button.text('Removed').addClass('disabled');
                    button.closest('.user-result').fadeOut(1000, function() {
                        $(this).remove();
                    });
                    setTimeout(() => {
                        $('#friends-search').trigger('input');
                    }, 700);
                } else {
                    hideButtonLoading(button);
                    showButtonFailed(button);
                }
            },
            error: function(xhr, status, error) {
                console.error('Remove friend error:', error);
                hideButtonLoading(button);
                showButtonFailed(button);
            }
        });
    });

    $(document).on('click', '.decline-button', function(e) {
        e.preventDefault();
        const button = $(this);
        const targetUsername = button.data('username');
        showButtonLoading(button);

        $.ajax({
            url: '/friends/decline',
            method: 'POST',
            data: { target_username: targetUsername },
            success: function(response) {
                if (response.status === 'success') {
                    hideButtonLoading(button);
                    keepButtonSize(button);
                    button.closest('.user-result').find('.accept-button').addClass('disabled');
                    button.text('Removed').addClass('disabled');
                    button.closest('.user-result').fadeOut(1000, function() {
                        $(this).remove();
                        $('#friends-search').trigger('input');
                    });
                } else {
                    hideButtonLoading(button);
                    showButtonFailed(button);
                }
            },
            error: function(xhr, status, error) {
                console.error('Decline friend invitation error:', error);
                hideButtonLoading(button);
                showButtonFailed(button);
            }
        });
    });
});