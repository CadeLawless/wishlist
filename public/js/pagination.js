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
    let contentSelector;
    if ($('.wishlist-grid').length) {
        contentSelector = '.wishlist-grid';
    } else if ($('.admin-table-body').length) {
        contentSelector = '.admin-table-body';
    } else {
        contentSelector = '.items-list.main';
    }
    
    // Function to build URL with preserved parameters
    function buildUrlWithParams(pageNumber) {
        const searchParams = new URLSearchParams(window.location.search);
        
        // Get base URL without query parameters for cleaner URL building
        let baseUrl = paginationState.baseUrl;
        // Remove query parameters from baseUrl if present
        if (baseUrl.includes('?')) {
            baseUrl = baseUrl.split('?')[0];
        }
        
        // Build URL with base URL and pagination
        let url = baseUrl + '?pageno=' + pageNumber;
        
        // Preserve existing URL parameters (id, key, search, etc.)
        if (searchParams.has('id')) {
            url += '&id=' + searchParams.get('id');
        }
        if (searchParams.has('key')) {
            url += '&key=' + searchParams.get('key');
        }
        if (searchParams.has('search')) {
            url += '&search=' + encodeURIComponent(searchParams.get('search'));
        } else {
            // Also check search input field if URL doesn't have search param
            const $searchInput = $('.admin-table-search-input');
            if ($searchInput.length && $searchInput.val().trim()) {
                url += '&search=' + encodeURIComponent($searchInput.val().trim());
            }
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
        updateBaseUrl: function(baseUrl) {
            paginationState.baseUrl = baseUrl;
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
            // Handle admin URLs with query parameters differently
            let paginateUrl;
            let ajaxData = { new_page: newPage };
            
            if (paginationState.baseUrl.includes('/admin/wishlists/view')) {
                // For admin wishlist view, use admin-specific pagination endpoint
                const urlParams = new URLSearchParams(paginationState.baseUrl.split('?')[1] || '');
                const id = urlParams.get('id') || '';
                paginateUrl = '/admin/wishlists/paginate-items?id=' + id;
            } else if (paginationState.baseUrl.includes('/admin/') || window.location.pathname.includes('/admin/')) {
                // For admin pages, use admin pagination endpoints and include search term
                // Use current pathname to determine endpoint (more reliable than baseUrl which may have query params)
                const pathname = window.location.pathname;
                if (pathname.includes('/admin/users')) {
                    paginateUrl = '/admin/users/paginate';
                } else if (pathname.includes('/admin/backgrounds')) {
                    paginateUrl = '/admin/backgrounds/paginate';
                } else if (pathname.includes('/admin/gift-wraps')) {
                    paginateUrl = '/admin/gift-wraps/paginate';
                } else if (pathname.includes('/admin/wishlists')) {
                    paginateUrl = '/admin/wishlists/paginate';
                } else {
                    // Fallback to using baseUrl if pathname check doesn't match
                    const basePath = paginationState.baseUrl.split('?')[0]; // Remove query params
                    paginateUrl = basePath + "/paginate";
                }
                // Include search term - check both URL and search input field
                const urlParams = new URLSearchParams(window.location.search);
                let searchTerm = urlParams.get('search') || '';
                // Also check search input field in case URL doesn't have it yet
                if (!searchTerm) {
                    const $searchInput = $('.admin-table-search-input');
                    if ($searchInput.length) {
                        searchTerm = $searchInput.val() || '';
                    }
                }
                // Always include search parameter (even if empty) to maintain consistency
                ajaxData.search = searchTerm;
            } else {
                // For regular wishlist/item pagination
                // The route is /wishlists/{id}/paginate (not /paginate-items)
                // Strip query parameters from baseUrl before appending /paginate
                let baseUrl = paginationState.baseUrl;
                if (baseUrl.includes('?')) {
                    baseUrl = baseUrl.split('?')[0];
                }
                paginateUrl = baseUrl + "/paginate";
                
                // Include search term if present in URL (for items pages)
                const urlParams = new URLSearchParams(window.location.search);
                const searchTerm = urlParams.get('search') || '';
                // Also check search input field in case URL doesn't have it yet
                if (!searchTerm) {
                    const $searchInput = $('.items-search-input');
                    if ($searchInput.length) {
                        const inputSearchTerm = $searchInput.val() || '';
                        if (inputSearchTerm) {
                            ajaxData.search = inputSearchTerm;
                        }
                    }
                } else {
                    ajaxData.search = searchTerm;
                }
            }
            
            $.ajax({
                type: "POST",
                url: paginateUrl,
                data: ajaxData,
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
                        if (data.paginationInfo) {
                            $('.paginate-container.bottom .count-showing, .count-showing').text(data.paginationInfo);
                        }
                        
                        // Update pagination controls visibility based on results (admin pages and items pages)
                        if ((contentSelector === '.admin-table-body' || contentSelector === '.items-list.main') && data.totalRows !== undefined) {
                            const isAdminPage = contentSelector === '.admin-table-body';
                            const itemsPerPage = data.itemsPerPage || (isAdminPage ? 10 : 12);
                            const totalRows = data.totalRows || 0;
                            const totalPages = data.total || 1;
                            const $paginationContainer = $('.paginate-container.bottom');
                            
                            // Get the wrapper element (center div)
                            const $paginationWrapper = $paginationContainer.closest('.center');
                            
                            // Show pagination controls if there are more results than items per page
                            if (totalRows > itemsPerPage) {
                                // Remove any inline display:none style and show the wrapper
                                $paginationWrapper.css('display', '');
                                $paginationWrapper.show();
                                // Show all pagination controls (arrows, title, and count)
                                $paginationContainer.find('.paginate-arrow, .paginate-title').show();
                                $paginationContainer.find('.count-showing').show();
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
                            
                            // Update top pagination controls for items pages
                            if (contentSelector === '.items-list.main' && !isAdminPage) {
                                const $topPaginationContainer = $('.paginate-container').not('.bottom').first();
                                if ($topPaginationContainer.length) {
                                    // Hide top pagination if there are no results or 12 or fewer results
                                    if (totalRows === 0 || totalRows <= itemsPerPage) {
                                        $topPaginationContainer.closest('.center').hide();
                                    } else {
                                        // Show top pagination if there are more than itemsPerPage results
                                        $topPaginationContainer.closest('.center').show();
                                        // Update page numbers in top pagination
                                        $topPaginationContainer.find('.page-number').text(data.current);
                                        $topPaginationContainer.find('.last-page').text(data.total);
                                    }
                                }
                            }
                        }
                        
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
                        
                        // Update URL without page refresh, preserving existing parameters (including search)
                        const newUrl = buildUrlWithParams(data.current);
                        history.replaceState(null, null, newUrl);
                        
                        // Also update base URL if search parameter exists (for both admin and items pages)
                        const urlParams = new URLSearchParams(window.location.search);
                        if (urlParams.has('search') && (contentSelector === '.admin-table-body' || contentSelector === '.items-list.main')) {
                            const pathname = window.location.pathname;
                            const searchTerm = urlParams.get('search');
                            const newBaseUrl = pathname + '?search=' + encodeURIComponent(searchTerm);
                            if (window.Pagination && window.Pagination.updateBaseUrl) {
                                window.Pagination.updateBaseUrl(newBaseUrl);
                            }
                        }
                        
                        // Scroll behavior based on page type
                        const isAdminPage = contentSelector === '.admin-table-body';
                        
                        if (isAdminPage) {
                            // For admin pages, scroll to the header text (h2.items-list-title)
                            const $headerText = $('.items-list-title').first();
                            if ($headerText.length) {
                                // Get header height dynamically (header height varies by screen size)
                                const $header = $('.header-container, .header').first();
                                const headerHeight = $header.length ? $header.outerHeight(true) : 0;
                                
                                // Calculate position: header text top position minus header height, with small padding
                                const headerTextOffset = $headerText.offset().top;
                                const scrollPosition = headerTextOffset - headerHeight - 10; // 10px padding
                                
                                window.scrollTo({ 
                                    top: Math.max(0, scrollPosition), 
                                    behavior: 'smooth' 
                                });
                            } else {
                                // Fallback to scrolling to top of page
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            }
                        } else {
                            // For other pages, scroll to top pagination controls
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
