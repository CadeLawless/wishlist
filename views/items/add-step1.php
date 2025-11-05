<?php
// Get wishlist data
$wishlistID = $wishlist['id'];
$wishlistTitle = htmlspecialchars($wishlist['wishlist_name']);
$background_image = $wishlist['background_image'] ?? '';
?>
<?php if($background_image != ""){ ?>
    <img class='background-theme desktop-background' src="/public/images/site-images/themes/desktop-backgrounds/<?php echo $background_image; ?>" />
    <img class='background-theme mobile-background' src="/public/images/site-images/themes/mobile-backgrounds/<?php echo $background_image; ?>" />
<?php } ?>
<p style="padding-top: 15px;"><a class="button accent" href="/<?php echo $wishlistID; ?>">Back to List</a></p>
<div class="center">
    <div class="wishlist-header center transparent-background">
        <h1><?php echo $wishlistTitle; ?></h1>
    </div>
</div>
<div class="form-container">
    <h2>🔗 Paste Product URL</h2>
    <p class="url-description">Paste any product URL from Amazon, eBay, or other e-commerce sites to automatically fill in the details.</p>
    
    <form id="url-fetch-form" class="url-fetch-form">
        <div class="url-input-group">
            <input 
                type="url" 
                id="product-url" 
                name="url" 
                placeholder="https://www.amazon.com/dp/B0F1XS8ZK4" 
                required
                class="large-url-input"
            >
            <button type="submit" id="fetch-details-btn" class="fetch-button">
                <span class="button-text">Fetch Details</span>
            </button>
        </div>
        <div id="loading-animation" class="loading-container hidden">
            <div class="loading-spinner"></div>
            <div class="loading-success hidden">
                <svg class="checkmark" viewBox="0 0 24 24">
                    <path class="checkmark-path" d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
            </div>
            <div class="loading-error hidden">
                <svg class="error-x" viewBox="0 0 24 24">
                    <path class="error-x-path" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </div>
        </div>
    </form>
    <p id="manual-link">Rather enter all the details yourself? <a href="/<?php echo $wishlist['id']; ?>/item/create">Add Item Manually</a></p>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('url-fetch-form');
    const urlInput = document.getElementById('product-url');
    const fetchBtn = document.getElementById('fetch-details-btn');
    const loadingContainer = document.getElementById('loading-animation');
    const loadingSpinner = loadingContainer.querySelector('.loading-spinner');
    const loadingSuccess = loadingContainer.querySelector('.loading-success');
    const loadingError = loadingContainer.querySelector('.loading-error');
    const manualLink = document.getElementById('manual-link');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchUrlDetails();
    });
    
    // Note: Auto-fetch on paste has been removed - users must click "Fetch Details" button
    
    function fetchUrlDetails() {
        const url = urlInput.value.trim();
        
        if (!url) {
            return;
        }
        
        if (!isValidUrl(url)) {
            return;
        }
        
        showLoadingState(true);
        
        fetch('/<?php echo $wishlist['id']; ?>/api/fetch-url-metadata', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'url=' + encodeURIComponent(url)
        })
        .then(response => {
            // Check if response is ok
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            
            // Get response text first to debug
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            
            // Try to parse as JSON
            try {
                const data = JSON.parse(text);
                return data;
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                throw new Error('Invalid JSON response: ' + text.substring(0, 100));
            }
        })
        .then(data => {
            if (data.success) {
                showSuccessAnimation();
                
                // Store data in session via AJAX
                fetch('/<?php echo $wishlist['id']; ?>/api/store-fetched-data', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'title=' + encodeURIComponent(data.title || '') + 
                          '&price=' + encodeURIComponent(data.price || '') + 
                          '&link=' + encodeURIComponent(url) + 
                          '&image=' + encodeURIComponent(data.image || '') +
                          '&product_details=' + encodeURIComponent(data.product_details || '') +
                          '&fetch_error=false'
                })
                .then(() => {
                    // Redirect to form after animation
                    setTimeout(() => {
                        window.location.href = '/<?php echo $wishlist['id']; ?>/item/create';
                    }, 1200);
                })
                .catch(error => {
                    console.error('Error storing data:', error);
                    // Still redirect even if storage fails
                    setTimeout(() => {
                        window.location.href = '/<?php echo $wishlist['id']; ?>/item/create';
                    }, 1200);
                });
            } else {
                showErrorAnimation();
                
                // Store error state and redirect
                fetch('/<?php echo $wishlist['id']; ?>/api/store-fetched-data', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                body: 'link=' + encodeURIComponent(url) + 
                      '&fetch_error=true&error_message=' + encodeURIComponent(data.error || 'Couldn\'t find product details for this URL - you\'ll need to fill them in manually!')
                })
                .then(() => {
                    // Redirect to form after animation
                    setTimeout(() => {
                        window.location.href = '/<?php echo $wishlist['id']; ?>/item/create';
                    }, 1000);
                })
                .catch(error => {
                    console.error('Error storing data:', error);
                    // Still redirect even if storage fails
                    setTimeout(() => {
                        window.location.href = '/<?php echo $wishlist['id']; ?>/item/create';
                    }, 1000);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorAnimation();
            
            // Store error state and redirect
            fetch('/<?php echo $wishlist['id']; ?>/api/store-fetched-data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'link=' + encodeURIComponent(url) + 
                      '&fetch_error=true&error_message=' + encodeURIComponent('Couldn\'t find product details for this URL - you\'ll need to fill them in manually!')
            })
            .then(() => {
                // Redirect to form after animation
                setTimeout(() => {
                    window.location.href = '/<?php echo $wishlist['id']; ?>/item/create';
                }, 1000);
            })
            .catch(error => {
                console.error('Error storing data:', error);
                // Still redirect even if storage fails
                setTimeout(() => {
                    window.location.href = '/<?php echo $wishlist['id']; ?>/item/create';
                }, 1000);
            });
        });
    }
    
    function showLoadingState(loading) {
        if (loading) {
            fetchBtn.disabled = true;
            fetchBtn.querySelector('.button-text').textContent = 'Fetching...';
            loadingContainer.classList.remove('hidden');
            loadingSpinner.classList.remove('hidden');
            loadingSuccess.classList.add('hidden');
            loadingError.classList.add('hidden');
            manualLink.style.display = 'none';
        } else {
            fetchBtn.disabled = false;
            fetchBtn.querySelector('.button-text').textContent = 'Fetch Details';
            loadingContainer.classList.add('hidden');
            manualLink.style.display = 'block';
        }
    }
    
    function showSuccessAnimation() {
        loadingSpinner.classList.add('hidden');
        loadingSuccess.classList.remove('hidden');
        // Animation will be handled by CSS
    }
    
    function showErrorAnimation() {
        loadingSpinner.classList.add('hidden');
        loadingError.classList.remove('hidden');
        // Animation will be handled by CSS
    }
    
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
});
</script>
