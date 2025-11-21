$(document).ready(function () {

    var croppieInstance = $('#croppie-container').croppie({
        viewport: { width: 200, height: 200, type: 'circle' },
        boundary: { width: 300, height: 300 },
        enableExif: true
    });

    // Load the selected image into Croppie
    $('#upload').on('change', function () {
        var reader = new FileReader();

        reader.onload = function (e) {
            croppieInstance.croppie('bind', {
                url: e.target.result
            });
        };

        reader.readAsDataURL(this.files[0]);
        $('#croppie-container').removeClass('hidden');
    });

    // Crop button
    $('#crop-btn').on('click', function () {
        const btn = $(this);
        showButtonLoading(btn);
        croppieInstance.croppie('result', {
            type: 'base64',
            size: 'viewport',
            format: 'png'
        }).then(function (base64) {

            // Show preview
            //$('#result').attr('src', base64).show();

            $.ajax({
                url: '/profile/upload-profile-picture',
                type: 'POST',
                data: { picture: base64 },
                success: function(response){
                    hideButtonLoading(btn);
                    if(response.success){
                        $('#croppie-container').addClass('hidden');
                        // Update profile picture on the page
                        if(($('.user-profile-picture .placeholder').length)) {
                            $('.user-profile-picture .placeholder').remove();
                            $('.user-profile-picture').prepend('<img class="profile-picture popup-button" src="' + base64 + '" alt="Profile Picture" />');
                        } else {
                            $('.user-profile-picture img').attr('src', base64);
                        }
                        $('#upload').val('');
                        btn.closest('.popup').find('.close-container').click();
                    } else {
                        if(btn.closest('.popup-content').find('.error-message').length) {
                            btn.closest('.popup-content').find('.error-message').remove();
                        }
                        btn.closest('.popup-content').append('<div class="error-message">An error occurred while uploading the picture. Please try again.</div>');
                    }

                },
                error: function(xhr, status, error){
                    hideButtonLoading(btn);
                    if(btn.closest('.popup-content').find('.error-message').length) {
                        btn.closest('.popup-content').find('.error-message').remove();
                    }
                    btn.closest('.popup-content').append('<div class="error-message">An error occurred while uploading the picture. Please try again.</div>');
                    console.error('AJAX Error: ' + status + error);
                }
            });
        });
    });

});
