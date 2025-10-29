/**
 * Wishlists Page Pagination Functionality
 * Handles pagination of wishlists on the index page
 */

$(document).ready(function() {
    
    // Initialize pagination state from the wishlist-grid element
    const $wishlistGrid = $(".wishlist-grid");
    
    const paginationState = {
        currentPage: parseInt($wishlistGrid.data('current-page')) || 1,
        totalPages: parseInt($wishlistGrid.data('total-pages')) || 1,
        baseUrl: $wishlistGrid.data('base-url') || ''
    };
    
    // Expose state management globally
    window.WishlistsPagination = {
        updateState: function(current, total) {
            paginationState.currentPage = current;
            paginationState.totalPages = total;
        },
        getState: function() {
            return { ...paginationState };
        }
    };
    
    // Pagination AJAX functionality - use event delegation
    $(document).on("click", ".paginate-arrow", function(e) {
        e.preventDefault();
        
        if ($(this).hasClass("disabled")) {
            return;
        }
        
        let newPage = paginationState.currentPage;
        
        if ($(this).hasClass("paginate-first")) {
            newPage = 1;
        } else if ($(this).hasClass("paginate-previous")) {
            newPage = Math.max(1, paginationState.currentPage - 1);
        } else if ($(this).hasClass("paginate-next")) {
            newPage = Math.min(paginationState.totalPages, paginationState.currentPage + 1);
        } else if ($(this).hasClass("paginate-last")) {
            newPage = paginationState.totalPages;
        }
        
        if (newPage !== paginationState.currentPage) {
            $.ajax({
                type: "POST",
                url: paginationState.baseUrl + "/paginate",
                data: { new_page: newPage },
                dataType: "json",
                success: function(data) {
                    // jQuery automatically parses JSON when dataType is "json"
                    
                    if (data.status === 'success') {
                        // Update wishlists grid HTML
                        $(".wishlist-grid").html(data.html);
                        
                        // Update pagination controls
                        $('.page-number').text(data.current);
                        $('.last-page').text(data.total);
                        $('.count-showing').text(data.paginationInfo);
                        
                        // Update arrow states based on new page
                        const totalPages = parseInt(data.total);
                        
                        // First and Previous arrows
                        $('.paginate-first, .paginate-previous').each(function() {
                            if (data.current <= 1) {
                                $(this).addClass('disabled');
                            } else {
                                $(this).removeClass('disabled');
                            }
                        });
                        
                        // Next and Last arrows
                        $('.paginate-next, .paginate-last').each(function() {
                            if (data.current >= totalPages) {
                                $(this).addClass('disabled');
                            } else {
                                $(this).removeClass('disabled');
                            }
                        });
                        
                        // Update URL without page refresh
                        const newUrl = paginationState.baseUrl + "?pageno=" + data.current;
                        history.pushState(null, null, newUrl);
                        
                        // Update the pagination variables for next pagination
                        paginationState.currentPage = data.current;
                        paginationState.totalPages = data.total;
                    } else {
                        console.error('Pagination error:', data.message);
                        alert('Pagination failed: ' + data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Pagination failed:', error);
                    alert('Pagination failed. Please try again.');
                }
            });
        }
    });
    
});
