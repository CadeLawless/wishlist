$(document).ready(function() {
    FormValidator.init('form', {
        theme_name: {
            required: true,
            minLength: 1,
            maxLength: 100
        },
        theme_tag: {
            required: true
        },
        theme_image: {
            required: true,
            minLength: 1,
            maxLength: 255
        },
        default_gift_wrap: {
            numeric: true
        }
    });
    
    // Handle desktop background image preview
    $('#desktop_background').on('change', function() {
        const file = this.files && this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const $preview = $('#desktop_preview');
                const $container = $('#desktop_preview_container');
                if ($preview.length && $container.length) {
                    $preview.attr('src', e.target.result).css('display', '');
                    $container.removeClass('hidden');
                }
            };
            reader.onerror = function() {
                console.error('Error reading desktop background file');
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Handle mobile background image preview
    $('#mobile_background').on('change', function() {
        const file = this.files && this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const $preview = $('#mobile_preview');
                const $container = $('#mobile_preview_container');
                if ($preview.length && $container.length) {
                    $preview.attr('src', e.target.result).css('display', '');
                    $container.removeClass('hidden');
                }
            };
            reader.onerror = function() {
                console.error('Error reading mobile background file');
            };
            reader.readAsDataURL(file);
        }
    });
    
    // File input click handlers
    $('.file-input').on('click', function(e) {
        e.preventDefault();
        const $fileInput = $(this).next('input[type="file"]');
        if ($fileInput.length) {
            $fileInput.trigger('click');
        }
    });
});