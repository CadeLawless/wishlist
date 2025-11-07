/**
 * Wishlist Filters Functionality
 * Handles sorting and filtering of wishlist items
 */

$(document).ready(function() {
    
    // Filter change event (triggered when select values change)
    $(".select-filter").on("change", function() {
        
        // Get search term from URL or search input field
        const urlParams = new URLSearchParams(window.location.search);
        let searchTerm = urlParams.get('search') || '';
        // Also check search input field if URL doesn't have it
        if (!searchTerm) {
            const $searchInput = $('.items-search-input');
            if ($searchInput.length) {
                searchTerm = $searchInput.val() || '';
            }
        }
        
        const formData = {
            sort_priority: $("#sort-priority").val(),
            sort_price: $("#sort-price").val(),
            search: searchTerm // Include search term in filter request
        };
        
        
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
                
                if (data.status === 'success') {
                    // Update items HTML
                    $(".items-list.main").html(data.html);
                    
                    // Update pagination info
                    const $countShowing = $('.paginate-container.bottom .count-showing');
                    if ($countShowing.length) {
                        $countShowing.text(data.paginationInfo);
                    }
                    
                    // Update pagination controls visibility and states
                    updatePaginationAfterFilter(data);
                    
                    // Update top pagination controls for items pages
                    updateTopPaginationAfterFilter(data);
                    
                    // Update page number display
                    $('.page-number').text(data.current);
                    $('.last-page').text(data.total);
                    
                    // Build URL with search term and update without page refresh
                    let newUrl = baseUrl + "?pageno=1";
                    if (searchTerm) {
                        newUrl += "&search=" + encodeURIComponent(searchTerm);
                    }
                    history.pushState(null, null, newUrl);
                    
                    // Update the pagination variables for next pagination
                    if (window.Pagination) {
                        window.Pagination.updateState(data.current, data.total);
                        // Update base URL to preserve search term
                        if (window.Pagination.updateBaseUrl) {
                            window.Pagination.updateBaseUrl(newUrl.split('?')[0] + (searchTerm ? '?search=' + encodeURIComponent(searchTerm) : ''));
                        }
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
    
    // Update bottom pagination controls after filter
    function updatePaginationAfterFilter(data) {
        const itemsPerPage = parseInt(data.itemsPerPage) || 12;
        const totalRows = parseInt(data.totalRows) || 0;
        const totalPages = parseInt(data.total) || 1;
        const currentPage = parseInt(data.current) || 1;
        
        const $paginationContainer = $('.paginate-container.bottom');
        const $paginationWrapper = $paginationContainer.closest('.center');
        
        if (totalRows > itemsPerPage) {
            // Remove any inline display:none style and show the wrapper
            $paginationWrapper.css('display', '');
            $paginationWrapper.show();
            // Show all pagination controls (arrows, title, and count)
            $paginationContainer.find('.paginate-arrow, .paginate-title').show();
            $paginationContainer.find('.count-showing').show();
            
            // Update arrow states
            const $firstArrow = $paginationContainer.find('.paginate-first');
            const $prevArrow = $paginationContainer.find('.paginate-previous');
            const $nextArrow = $paginationContainer.find('.paginate-next');
            const $lastArrow = $paginationContainer.find('.paginate-last');
            
            // First and Previous arrows - disabled on page 1
            if ($firstArrow.length) {
                if (currentPage <= 1) {
                    $firstArrow.addClass('disabled');
                } else {
                    $firstArrow.removeClass('disabled');
                }
            }
            if ($prevArrow.length) {
                if (currentPage <= 1) {
                    $prevArrow.addClass('disabled');
                } else {
                    $prevArrow.removeClass('disabled');
                }
            }
            
            // Next and Last arrows - disabled on last page
            if ($nextArrow.length) {
                if (currentPage >= totalPages) {
                    $nextArrow.addClass('disabled');
                } else {
                    $nextArrow.removeClass('disabled');
                }
            }
            if ($lastArrow.length) {
                if (currentPage >= totalPages) {
                    $lastArrow.addClass('disabled');
                } else {
                    $lastArrow.removeClass('disabled');
                }
            }
        } else if (totalRows > 0 && totalRows <= itemsPerPage) {
            // Remove any inline display:none style and show the wrapper
            $paginationWrapper.css('display', '');
            $paginationWrapper.show();
            // Show only count, hide pagination arrows (results fit on one page)
            $paginationContainer.find('.paginate-arrow, .paginate-title').hide();
            $paginationContainer.find('.count-showing').show();
        } else {
            // Hide everything if no results
            $paginationWrapper.hide();
        }
    }
    
    // Update top pagination controls after filter
    function updateTopPaginationAfterFilter(data) {
        const itemsPerPage = parseInt(data.itemsPerPage) || 12;
        const totalRows = parseInt(data.totalRows) || 0;
        const totalPages = parseInt(data.total) || 1;
        const currentPage = parseInt(data.current) || 1;
        
        const $topPaginationContainer = $('.paginate-container').not('.bottom').first();
        
        if ($topPaginationContainer.length) {
            const $topPaginationWrapper = $topPaginationContainer.closest('.center');
            
            // Hide top pagination if there are no results or 12 or fewer results
            if (totalRows === 0 || totalRows <= itemsPerPage) {
                $topPaginationWrapper.hide();
            } else {
                // Show top pagination if there are more than itemsPerPage results
                $topPaginationWrapper.css('display', '');
                $topPaginationWrapper.show();
                
                // Update page numbers in top pagination
                const $topPageNumber = $topPaginationContainer.find('.page-number');
                const $topLastPage = $topPaginationContainer.find('.last-page');
                if ($topPageNumber.length) {
                    $topPageNumber.text(currentPage);
                }
                if ($topLastPage.length) {
                    $topLastPage.text(totalPages);
                }
                
                // Update arrow states for top pagination
                const $firstArrow = $topPaginationContainer.find('.paginate-first');
                const $prevArrow = $topPaginationContainer.find('.paginate-previous');
                const $nextArrow = $topPaginationContainer.find('.paginate-next');
                const $lastArrow = $topPaginationContainer.find('.paginate-last');
                
                // First and Previous arrows - disabled on page 1
                if ($firstArrow.length) {
                    if (currentPage <= 1) {
                        $firstArrow.addClass('disabled');
                    } else {
                        $firstArrow.removeClass('disabled');
                    }
                }
                if ($prevArrow.length) {
                    if (currentPage <= 1) {
                        $prevArrow.addClass('disabled');
                    } else {
                        $prevArrow.removeClass('disabled');
                    }
                }
                
                // Next and Last arrows - disabled on last page
                if ($nextArrow.length) {
                    if (currentPage >= totalPages) {
                        $nextArrow.addClass('disabled');
                    } else {
                        $nextArrow.removeClass('disabled');
                    }
                }
                if ($lastArrow.length) {
                    if (currentPage >= totalPages) {
                        $lastArrow.addClass('disabled');
                    } else {
                        $lastArrow.removeClass('disabled');
                    }
                }
            }
        }
    }
    
});
