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
