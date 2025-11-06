/**
 * Generic Pagination Functionality
 * Handles pagination for any page type (wishlists, wishlist items, etc.)
 */

$(document).ready(function() {
    
    // Initialize pagination state from body element
    const paginationState = {
        currentPage: parseInt($('body').data('current-page')) || 1,
        totalPages: parseInt($('body').data('total-pages')) || 1,
        baseUrl: $('body').data('base-url') || ''
    };
    
    // Determine the content selector based on what exists on the page
    // This allows the same script to work for different page types
    const contentSelector = $('.wishlist-grid').length ? '.wishlist-grid' : '.items-list.main';
    
    // Function to build URL with preserved parameters
    function buildUrlWithParams(pageNumber) {
        const searchParams = new URLSearchParams(window.location.search);
        
        // Build URL with base URL and pagination
        let url = paginationState.baseUrl + '?pageno=' + pageNumber;
        
        // Preserve existing URL parameters (id, key, etc.)
        if (searchParams.has('id')) {
            url += '&id=' + searchParams.get('id');
        }
        if (searchParams.has('key')) {
            url += '&key=' + searchParams.get('key');
        }
        
        return url;
    }
    
    // Handle browser back/forward buttons
    window.addEventListener("popstate", function() {
        window.location.reload();
    });
    
    // Initialize history state on page load
    window.history.pushState({}, "", window.location.href);
    
    // Expose state management globally
    window.Pagination = {
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
                        // Update content HTML dynamically based on page type
                        $(contentSelector).html(data.html);
                        
                        // Update pagination controls
                        $('.page-number').text(data.current);
                        $('.last-page').text(data.total);
                        // Update count-showing if it exists (inside bottom paginate-container)
                        $('.paginate-container.bottom .count-showing, .count-showing').text(data.paginationInfo);
                        
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
                        
                        // Update URL without page refresh, preserving existing parameters
                        const newUrl = buildUrlWithParams(data.current);
                        history.replaceState(null, null, newUrl);
                        
                        // Scroll to top pagination controls on page change, accounting for fixed header
                        const $topPagination = $('.paginate-container').first();
                        if ($topPagination.length) {
                            // Get header height dynamically (header height varies by screen size)
                            const $header = $('.header-container, .header').first();
                            const headerHeight = $header.length ? $header.outerHeight(true) : 0;
                            
                            // Calculate position: pagination top position minus header height, with small padding
                            const paginationOffset = $topPagination.offset().top;
                            const scrollPosition = paginationOffset - headerHeight - 10; // 10px padding
                            
                            window.scrollTo({ 
                                top: Math.max(0, scrollPosition), 
                                behavior: 'smooth' 
                            });
                        } else {
                            // Fallback to scrolling to top of page
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                        
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
