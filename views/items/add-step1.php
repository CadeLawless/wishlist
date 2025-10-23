<?php
// Get wishlist data
$wishlistID = $wishlist['id'];
$wishlistTitle = htmlspecialchars($wishlist['wishlist_name']);
$background_image = $wishlist['background_image'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/wishlist/">
    <link rel="icon" type="image/x-icon" href="public/images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="/wishlist/public/css/styles.css" />
    <title><?php echo $wishlistTitle; ?> | Add Item</title>
    <style>
        #body {
            padding-top: 84px;
        }
        h1 {
            display: inline-block;
            margin-top: 0;
        }
        #container .background-theme.mobile-background {
            display: none;
        }
        @media (max-width: 600px){
            #container .background-theme.mobile-background {
                display: block;
            }
            #container .background-theme.desktop-background {
                display: none;
            }
        }
    </style>
</head>
<body class="<?php echo $user['dark'] === 'Yes' ? 'dark' : ''; ?>">
    <div id="body">
        <?php include __DIR__ . '/../components/header.php'; ?>
        <div id="container">
            <?php if($background_image != ""){ ?>
                <img class='background-theme desktop-background' src="/wishlist/public/images/site-images/themes/desktop-backgrounds/<?php echo $background_image; ?>" />
                <img class='background-theme mobile-background' src="/wishlist/public/images/site-images/themes/mobile-backgrounds/<?php echo $background_image; ?>" />
            <?php } ?>
            <p style="padding-top: 15px;"><a class="button accent" href="/wishlist/<?php echo $wishlistID; ?>">Back to List</a></p>
            <div class="center">
                <div class="wishlist-header center transparent-background">
                    <h1><?php echo $wishlistTitle; ?></h1>
                </div>
            </div>
            <div class="add-item-step1">
                <h1>Add New Item</h1>
                <p class="step-description">Paste a product URL to automatically fetch details, or add an item manually.</p>
        
        <div class="url-fetch-section">
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
                        <span id="fetch-spinner" class="hidden">⏳</span>
                    </button>
                </div>
                <div id="fetch-status" class="hidden"></div>
            </form>
        </div>
        
        <div class="divider">
            <span>or</span>
        </div>
        
        <div class="manual-add-section">
            <h2>✏️ Add Manually</h2>
            <p class="manual-description">Prefer to enter all details yourself? Click below to go straight to the item form.</p>
            <a href="/wishlist/<?php echo $wishlist['id']; ?>/item/create" class="manual-add-button">
                Add Item Manually
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('url-fetch-form');
    const urlInput = document.getElementById('product-url');
    const fetchBtn = document.getElementById('fetch-details-btn');
    const spinner = document.getElementById('fetch-spinner');
    const status = document.getElementById('fetch-status');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchUrlDetails();
    });
    
    // Auto-fetch on paste
    urlInput.addEventListener('paste', function() {
        setTimeout(() => {
            const url = urlInput.value.trim();
            if (url && isValidUrl(url)) {
                setTimeout(() => {
                    fetchUrlDetails();
                }, 500);
            }
        }, 100);
    });
    
    function fetchUrlDetails() {
        const url = urlInput.value.trim();
        
        if (!url) {
            showStatusMessage("Please enter a URL first", "error");
            return;
        }
        
        if (!isValidUrl(url)) {
            showStatusMessage("Please enter a valid URL", "error");
            return;
        }
        
        showLoadingState(true);
        showStatusMessage("Fetching product details...", "info");
        
        fetch('/wishlist/<?php echo $wishlist['id']; ?>/api/fetch-url-metadata', {
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
            showLoadingState(false);
            
            if (data.success) {
                showStatusMessage("Product details fetched successfully! Redirecting to form...", "success");
                
                // Store data in session via AJAX
                fetch('/wishlist/<?php echo $wishlist['id']; ?>/api/store-fetched-data', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'title=' + encodeURIComponent(data.title || '') + 
                          '&price=' + encodeURIComponent(data.price || '') + 
                          '&link=' + encodeURIComponent(url) + 
                          '&image=' + encodeURIComponent(data.image || '') +
                          '&product_details=' + encodeURIComponent(data.product_details || '')
                })
                .then(() => {
                    // Redirect to form (data will be loaded from session)
                    setTimeout(() => {
                        window.location.href = '/wishlist/<?php echo $wishlist['id']; ?>/item/create';
                    }, 1000);
                })
                .catch(error => {
                    console.error('Error storing data:', error);
                    // Still redirect even if storage fails
                    setTimeout(() => {
                        window.location.href = '/wishlist/<?php echo $wishlist['id']; ?>/item/create';
                    }, 1000);
                });
            } else {
                showStatusMessage(data.error || "Could not fetch product details", "error");
            }
        })
        .catch(error => {
            showLoadingState(false);
            console.error('Error:', error);
            showStatusMessage("An error occurred while fetching product details. Please try again.", "error");
        });
    }
    
    function showLoadingState(loading) {
        if (loading) {
            fetchBtn.disabled = true;
            fetchBtn.querySelector('.button-text').textContent = 'Fetching...';
            spinner.classList.remove('hidden');
        } else {
            fetchBtn.disabled = false;
            fetchBtn.querySelector('.button-text').textContent = 'Fetch Details';
            spinner.classList.add('hidden');
        }
    }
    
    function showStatusMessage(message, type) {
        status.textContent = message;
        status.className = type;
        status.classList.remove('hidden');
        
        if (type === 'success') {
            setTimeout(() => {
                status.classList.add('hidden');
            }, 3000);
        }
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
        </div>
    </div>
    <?php include __DIR__ . '/../components/footer.php'; ?>
    <script src="public/scripts/autosize-master/autosize-master/dist/autosize.js"></script>
</body>
</html>
