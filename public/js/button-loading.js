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
    button.addClass('disabled failed').find('span').text(button.data('fail-text'));
    if(button.hasClass('accept-button')) {
        button.closest('.user-result').find('.decline-button').addClass('disabled');
    }
    if(button.hasClass('decline-button')) {
        button.closest('.user-result').find('.accept-button').addClass('disabled');
    }
    setTimeout(() => {
        button.removeClass('disabled failed').find('span').text(button.data('original-text'));
        if(button.hasClass('accept-button')) {
            button.closest('.user-result').find('.decline-button').removeClass('disabled');
        }
        if(button.hasClass('decline-button')) {
            button.closest('.user-result').find('.accept-button').removeClass('disabled');
        }

        button.css({
            'width': '',
            'height': ''
        });
    }, 5000);
}
