/**
 * Wishlist Filters Functionality
 * Handles sorting and filtering of wishlist items
 */

$(document).ready(function() {
    
    // Filter change event (triggered when select values change)
    $(".select-filter").on("change", function() {
        console.log('Filter select changed');
        
        const formData = {
            sort_priority: $("#sort-priority").val(),
            sort_price: $("#sort-price").val()
        };
        
        console.log('Filter data:', formData);
        
        const baseUrl = $(this).data('base-url');
        if (!baseUrl) {
            console.error('Base URL not found for filter request');
            return;
        }
        
        $.ajax({
            type: "POST",
            url: baseUrl + "/filter",
            data: formData,
            dataType: "json",
            success: function(data) {
                console.log('Filter AJAX success, received data:', data);
                
                if (data.status === 'success') {
                    // Update items HTML
                    $(".items-list.main").html(data.html);
                    
                    // Update pagination controls
                    $('.page-number').text(data.current);
                    $('.last-page').text(data.total);
                    $('.count-showing').text(data.paginationInfo);
                    
                    // Update arrow states based on new page (always page 1 after filtering)
                    const totalPages = parseInt(data.total);
                    
                    // First and Previous arrows should be disabled (we're on page 1)
                    $('.paginate-first, .paginate-previous').each(function() {
                        $(this).addClass('disabled');
                    });
                    
                    // Next and Last arrows
                    $('.paginate-next, .paginate-last').each(function() {
                        if (totalPages <= 1) {
                            $(this).addClass('disabled');
                        } else {
                            $(this).removeClass('disabled');
                        }
                    });
                    
                    // Update URL without page refresh
                    const newUrl = baseUrl + "?pageno=1#paginate-top";
                    history.pushState(null, null, newUrl);
                    
                    // Update the pagination variables for next pagination
                    if (window.WishlistPagination) {
                        window.WishlistPagination.updateState(data.current, data.total);
                    }
                } else {
                    console.error('Filter error:', data.message);
                    alert('Filter failed: ' + data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Filter failed:', error);
                console.error('Response:', xhr.responseText);
                alert('Filter failed. Please try again.');
            }
        });
    });
    
});
