function reloadWishLists() {
    const params = new URLSearchParams(window.location.search);
    const pageno = params.get('pageno');
    $.ajax({
        url: window.location.pathname + "/reload?pageno=" + pageno,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            $('.wishlist-grid').html(response.html);
            if(response.count <= 12){
                $('.paginate-container').remove();
            } else {
                if ($('.paginate-container').length) {
                    $('.paginate-container .count-showing').html(response.pagination_text);
                    $('.paginate-container .page-number').html(response.pageno);
                    $('.paginate-container .last-page').html(response.total_pages);
                }
            }
        },
        error: function(xhr, status, error) {
            console.log('Status:', status);
            console.log('Error:', error);
            console.log('Response:', xhr.responseText);
            addAlertMessage('Failed to reload wish lists');
        }
    });
}