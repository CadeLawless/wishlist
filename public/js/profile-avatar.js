$(document).ready(function () {

    var croppieInstance = $('#croppie-container').croppie({
        viewport: { width: 200, height: 200, type: 'circle' },
        boundary: { width: 250, height: 250 },
        enableExif: false,     // avoid iOS crash issues
        enableOrientation: true
    });

    // Resize extremely large photos BEFORE using croppie
    function resizeImageForIOS(imgSrc, maxDim = 2000, callback) {
        const img = new Image();
        img.onload = function () {
            let width = img.width;
            let height = img.height;

            // Scale down if needed
            if (width > maxDim || height > maxDim) {
                const scale = Math.min(maxDim / width, maxDim / height);
                width = width * scale;
                height = height * scale;
            }

            // Canvas resize
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            const resizedDataUrl = canvas.toDataURL('image/jpeg', 0.9);
            callback(resizedDataUrl);
        };

        img.src = imgSrc;
    }

    // Load the selected image into Croppie
    $('#upload').on('change', function () {
        const file = this.files[0];
        const reader = new FileReader();

        reader.onload = function (e) {
            const originalImg = e.target.result;

            // Resize for iOS BEFORE Croppie binds to it
            resizeImageForIOS(originalImg, 2000, function (resizedImg) {
                croppieInstance.croppie('bind', {
                    url: resizedImg
                });
            });
        };

        reader.readAsDataURL(file);
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
