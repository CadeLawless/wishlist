/**
 * Reusable Table Search Filter
 * Works for both admin tables and items pages with debounce and server-side filtering
 */

$(document).ready(function() {
    
    /**
     * Initialize table search filter with server-side filtering
     * @param {string} searchInputSelector - Selector for the search input
     * @param {string} paginateUrl - URL for pagination AJAX endpoint
     * @param {Object} options - Configuration options
     * @param {number} options.debounceDelay - Delay in milliseconds (default: 300)
     * @param {string} options.contentSelector - Selector for content container (default: auto-detect)
     * @param {string} options.containerSelector - Selector for container with loading overlay (default: auto-detect)
     */
    function initTableSearch(searchInputSelector, paginateUrl, options = {}) {
        const debounceDelay = options.debounceDelay || 300;
        const $searchInput = $(searchInputSelector);
        
        // Auto-detect content selector (admin tables vs items)
        let contentSelector = options.contentSelector;
        let containerSelector = options.containerSelector;
        
        if (!contentSelector) {
            if ($('.admin-table-body').length) {
                contentSelector = '.admin-table-body';
            } else if ($('.items-list.main').length) {
                contentSelector = '.items-list.main';
            } else {
                return; // Required elements not found
            }
        }
        
        if (!containerSelector) {
            if ($('.admin-center-table-container').length) {
                containerSelector = '.admin-center-table-container';
            } else if ($('.items-content-container').length) {
                // For items pages, use items-content-container so overlay covers pagination and items but not search bar
                containerSelector = '.items-content-container';
            } else if ($('.items-list-sub-container').length) {
                // Fallback to items-list-sub-container
                containerSelector = '.items-list-sub-container';
            } else if ($('.items-list-container').length) {
                containerSelector = '.items-list-container';
            } else {
                containerSelector = contentSelector; // Fallback to content selector
            }
        }
        
        const $contentContainer = $(contentSelector);
        const $paginationContainer = $('.paginate-container.bottom');
        const $tableContainer = $(containerSelector);
        
        if (!$searchInput.length || !$contentContainer.length) {
            return; // Required elements not found
        }
        
        let debounceTimer = null;
        
        // Create loading overlay if it doesn't exist (use generic class name)
        let $loadingOverlay = $tableContainer.find('.table-search-loading-overlay');
        if (!$loadingOverlay.length) {
            $loadingOverlay = $('<div class="table-search-loading-overlay"><div class="loading-spinner"></div></div>');
            $tableContainer.append($loadingOverlay);
        }
        
        // Show loading overlay
        function showLoading() {
            $loadingOverlay.addClass('active');
        }
        
        // Hide loading overlay
        function hideLoading() {
            $loadingOverlay.removeClass('active');
        }
        
        // Get current page from pagination state or URL
        function getCurrentPage() {
            const $pageNumber = $('.page-number');
            if ($pageNumber.length) {
                return parseInt($pageNumber.text(), 10) || 1;
            }
            // Try to get from URL
            const urlParams = new URLSearchParams(window.location.search);
            return parseInt(urlParams.get('pageno'), 10) || 1;
        }
        
        // Perform search via AJAX
        function performSearch(searchTerm, page = 1) {
            // Update URL with search parameter
            const url = new URL(window.location.href);
            const hasSearchTerm = searchTerm.trim() !== '';
            if (hasSearchTerm) {
                url.searchParams.set('search', searchTerm);
                url.searchParams.set('pageno', page);
            } else {
                url.searchParams.delete('search');
                url.searchParams.set('pageno', page);
            }
            window.history.replaceState({}, '', url);
            
            // Store hasSearchTerm for use in callbacks (closure)
            const hasSearchTermForCallback = hasSearchTerm;
            
            $.ajax({
                type: "POST",
                url: paginateUrl,
                data: {
                    new_page: page,
                    search: searchTerm
                },
                dataType: "json",
                success: function(data) {
                    if (data.status === 'success') {
                        // Update content (works for both admin tables and items)
                        $contentContainer.html(data.html);
                        
                        // Check if we need to show empty state (only when there's a search term and no results)
                        const totalRows = data.totalRows || 0;
                        if (totalRows === 0 && hasSearchTermForCallback) {
                            // Check if this is an admin table (has thead) or items list
                            if ($contentContainer.hasClass('admin-table-body')) {
                                // Admin table empty state
                                const $table = $contentContainer.closest('table');
                                const $header = $table.find('thead tr').first();
                                const columnCount = $header.find('th').length;
                                const emptyStateHtml = '<tr class="admin-empty-state"><td colspan="' + columnCount + '" style="text-align: center; padding: 40px 20px; color: var(--text-secondary); font-size: 1.1em;">No results found</td></tr>';
                                $contentContainer.html(emptyStateHtml);
                            } else {
                                // Items list empty state
                                const emptyStateHtml = '<div class="center" style="padding: 40px 20px; color: var(--text-secondary); font-size: 1.1em;">No results found</div>';
                                $contentContainer.html(emptyStateHtml);
                            }
                        }
                        
                        // Update pagination info
                        const $countShowing = $paginationContainer.find('.count-showing');
                        if ($countShowing.length) {
                            $countShowing.text(data.paginationInfo);
                        }
                        
                        // Update pagination controls (bottom pagination)
                        updatePaginationControls(data, $paginationContainer);
                        
                        // Update top pagination controls for items pages
                        if ($contentContainer.hasClass('items-list') || contentSelector === '.items-list.main') {
                            updateTopPaginationControls(data, $contentContainer);
                        }
                        
                        // Update page number display
                        $('.page-number').text(data.current);
                        $('.last-page').text(data.total);
                        
                        // Update pagination state for pagination.js so arrow clicks work correctly
                        if (window.Pagination) {
                            if (window.Pagination.updateState) {
                                window.Pagination.updateState(data.current, data.total);
                            }
                            // Update base URL to preserve search term in pagination state
                            const currentUrl = new URL(window.location.href);
                            const pathname = currentUrl.pathname;
                            let newBaseUrl = pathname;
                            if (hasSearchTermForCallback) {
                                newBaseUrl = pathname + '?search=' + encodeURIComponent(searchTerm);
                            }
                            if (window.Pagination.updateBaseUrl) {
                                window.Pagination.updateBaseUrl(newBaseUrl);
                            }
                        }
                        
                        // Scroll to appropriate element based on page type
                        if ($contentContainer.hasClass('admin-table-body')) {
                            // Admin page: scroll to header
                            const $headerText = $('.items-list-title').first();
                            if ($headerText.length) {
                                const $header = $('.header-container, .header').first();
                                const headerHeight = $header.length ? $header.outerHeight(true) : 0;
                                const headerTextOffset = $headerText.offset().top;
                                const scrollPosition = headerTextOffset - headerHeight - 10;
                                window.scrollTo({ 
                                    top: Math.max(0, scrollPosition), 
                                    behavior: 'smooth' 
                                });
                            }
                        } else {
                            // Items page: scroll to items list title or top pagination
                            const $headerText = $('.items-list-title').first();
                            const $topPagination = $('.paginate-container').first();
                            const $scrollTarget = $headerText.length ? $headerText : $topPagination;
                            if ($scrollTarget.length) {
                                const $header = $('.header-container, .header').first();
                                const headerHeight = $header.length ? $header.outerHeight(true) : 0;
                                const targetOffset = $scrollTarget.offset().top;
                                const scrollPosition = targetOffset - headerHeight - 10;
                                window.scrollTo({ 
                                    top: Math.max(0, scrollPosition), 
                                    behavior: 'smooth' 
                                });
                            }
                        }
                    }
                    isLoading = false;
                    hideLoading();
                },
                error: function(xhr, status, error) {
                    console.error('Search error:', error);
                    isLoading = false;
                    hideLoading();
                },
                complete: function() {
                    // Ensure loading is hidden and flag is reset
                    isLoading = false;
                    hideLoading();
                }
            });
        }
        
        // Update pagination controls based on results
        function updatePaginationControls(data, $paginationContainer) {
            // Determine items per page based on page type (admin: 10, items: 12)
            const isAdminPage = $contentContainer.hasClass('admin-table-body');
            const itemsPerPage = data.itemsPerPage || (isAdminPage ? 10 : 12);
            const totalRows = data.totalRows || 0;
            const totalPages = data.total || 1;
            const currentPage = data.current || 1;
            
            // Show pagination controls if there are more results than items per page
            if (totalRows > itemsPerPage) {
                // Show all pagination controls (arrows, title, and count)
                $paginationContainer.find('.paginate-arrow, .paginate-title').show();
                $paginationContainer.find('.count-showing').show();
                $paginationContainer.show();
            } else if (totalRows > 0 && totalRows <= itemsPerPage) {
                // Show only count, hide pagination arrows (results fit on one page)
                $paginationContainer.find('.paginate-arrow, .paginate-title').hide();
                $paginationContainer.find('.count-showing').show();
                $paginationContainer.show();
            } else {
                // Hide everything if no results
                $paginationContainer.hide();
            }
        }
        
        // Update top pagination controls for items pages
        function updateTopPaginationControls(data, $contentContainer) {
            const itemsPerPage = data.itemsPerPage || 12;
            const totalRows = data.totalRows || 0;
            const totalPages = data.total || 1;
            const currentPage = data.current || 1;
            
            // Find top pagination container (not the bottom one)
            const $topPaginationContainer = $('.paginate-container').not('.bottom').first();
            
            if ($topPaginationContainer.length) {
                // Hide top pagination if there are no results or 12 or fewer results
                if (totalRows === 0 || totalRows <= itemsPerPage) {
                    $topPaginationContainer.closest('.center').hide();
                } else {
                    // Show top pagination if there are more than itemsPerPage results
                    $topPaginationContainer.closest('.center').show();
                    // Update page numbers in top pagination
                    $topPaginationContainer.find('.page-number').text(currentPage);
                    $topPaginationContainer.find('.last-page').text(totalPages);
                    
                    // Update arrow states for top pagination
                    const $topArrows = $topPaginationContainer.find('.paginate-arrow');
                    $topArrows.find('.paginate-first, .paginate-previous').each(function() {
                        if (currentPage <= 1) {
                            $(this).addClass('disabled');
                        } else {
                            $(this).removeClass('disabled');
                        }
                    });
                    $topArrows.find('.paginate-next, .paginate-last').each(function() {
                        if (currentPage >= totalPages) {
                            $(this).addClass('disabled');
                        } else {
                            $(this).removeClass('disabled');
                        }
                    });
                }
            }
        }
        
        // Initialize search term from URL if present (in case PHP didn't populate it)
        // This ensures the input is populated even if PHP didn't pass the search term
        const urlParamsInit = new URLSearchParams(window.location.search);
        const urlSearchTermInit = urlParamsInit.get('search') || '';
        if (urlSearchTermInit && !$searchInput.val()) {
            // Only set if input is empty (PHP should have set it, but this is a fallback)
            $searchInput.val(urlSearchTermInit);
        }
        
        // Track if we're currently loading to prevent showing loading on every keystroke
        let isLoading = false;
        
        // Initialize lastSearchTerm after ensuring input is populated
        let lastSearchTerm = $searchInput.val() || '';
        
        // Handle search input with debounce
        $searchInput.on('input', function() {
            const searchTerm = $(this).val();
            
            // Update clear button visibility
            const $clearBtn = $(this).siblings('.clear-search');
            if ($clearBtn.length) {
                if (searchTerm.trim() !== '') {
                    $clearBtn.show();
                } else {
                    $clearBtn.hide();
                }
            }
            
            // Show loading immediately when user types (if search term changed and not already loading)
            if (searchTerm !== lastSearchTerm && !isLoading) {
                showLoading();
                isLoading = true;
            }
            
            lastSearchTerm = searchTerm;
            
            // Clear existing timer
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }
            
            // Set new timer - reset to page 1 when searching
            debounceTimer = setTimeout(function() {
                performSearch(searchTerm, 1);
            }, debounceDelay);
        });
        
        // Handle clear button
        $searchInput.siblings('.clear-search').on('click', function(e) {
            e.preventDefault();
            $searchInput.val('').trigger('input');
            $searchInput.focus();
        });
        
        // Initialize clear button visibility based on current input value
        // (This runs after URL check above, so input should already be populated if needed)
        const $clearBtnInit = $searchInput.siblings('.clear-search');
        if ($clearBtnInit.length) {
            const currentValue = $searchInput.val() || '';
            if (currentValue.trim() !== '') {
                $clearBtnInit.show();
            } else {
                $clearBtnInit.hide();
            }
        }
    }
    
    // Auto-initialize search for admin tables and items pages
    $('.admin-table-search-input, .items-search-input').each(function() {
        const $searchInput = $(this);
        
        // Determine paginate URL based on current page
        const path = window.location.pathname;
        let paginateUrl = '';
        
        // Admin pages
        if (path.includes('/admin/users')) {
            paginateUrl = '/admin/users/paginate';
        } else if (path.includes('/admin/backgrounds')) {
            paginateUrl = '/admin/backgrounds/paginate';
        } else if (path.includes('/admin/gift-wraps')) {
            paginateUrl = '/admin/gift-wraps/paginate';
        } else if (path.includes('/admin/wishlists')) {
            // Check if it's the view page (items) or list page
            if (path.includes('/admin/wishlists/view')) {
                const urlParams = new URLSearchParams(window.location.search);
                const id = urlParams.get('id') || '';
                paginateUrl = '/admin/wishlists/paginate-items?id=' + id;
            } else {
                paginateUrl = '/admin/wishlists/paginate';
            }
        }
        // Items/wishlist pages
        else if (path.match(/^\/wishlists\/\d+$/)) {
            // Extract wishlist ID from path
            // The route is /wishlists/{id}/paginate (not /paginate-items)
            const match = path.match(/^\/wishlists\/(\d+)$/);
            if (match) {
                const wishlistId = match[1];
                paginateUrl = '/wishlists/' + wishlistId + '/paginate';
            }
        }
        
        if (paginateUrl) {
            initTableSearch($searchInput, paginateUrl, { debounceDelay: 300 });
        }
    });
});
