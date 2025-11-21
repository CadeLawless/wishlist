<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/public/images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="/public/css/styles.css?v=2.1" />
    <link rel="stylesheet" type="text/css" href="/public/css/snow.css?v=2.1" />
    <script src="https://cdn.jsdelivr.net/npm/tsparticles-confetti@2.10.0/tsparticles.confetti.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <title><?php echo htmlspecialchars($title ?? 'Any Wish List'); ?></title>
    <style>
        h1 {
            display: inline-block;
        }
        h2.items-list-title {
            position: relative;
        }
        .header .title {
            flex-basis: 100%;
        }
        .menu-links, .hamburger-menu, .close-menu {
            display: none !important;
        }
        
        /* Ensure navigation stays hidden even if JavaScript removes hidden class */
        body .menu-links,
        body .hamburger-menu, 
        body .close-menu {
            display: none !important;
        }
        
        /* Prevent any JavaScript from showing navigation in buyer view */
        .buyer-view .menu-links,
        .buyer-view .hamburger-menu,
        .buyer-view .close-menu {
            display: none !important;
        }
        .popup.fullscreen .gift-wrap-content .popup-content {
            max-height: calc(100% - 184px);
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
        @media (max-width: 460px){
            .popup.fullscreen .gift-wrap-content .popup-content {
                max-height: calc(100% - 223px);
            }
        }
        @media (max-width: 284px){
            .popup.fullscreen .gift-wrap-content .popup-content {
                max-height: calc(100% - 254px);
            }
        }
    </style>
    <?php if(isset($customStyles) && !empty($customStyles)): ?>
    <style>
    <?php echo $customStyles; ?>
    </style>
    <?php endif; ?>
</head>
<body class="buyer-view" 
      data-current-page="<?php echo $pageno ?? 1; ?>" 
      data-total-pages="<?php echo $total_pages ?? 1; ?>" 
      data-base-url="/buyer/<?php echo $wishlist['secret_key'] ?? ''; ?>">
    <div id="body">
        <?php include __DIR__ . '/../components/header.php'; ?>
        <div id="container">
            <?php echo $content; ?>
        </div>
    </div>
    <?php include __DIR__ . '/../components/footer.php'; ?>
    
    <script src="/public/js/wishlist-filters.js?v=2.1"></script>
    <script src="/public/js/pagination.js?v=2.1"></script>
    <script>
    // Client-side dark mode toggle for buyer view (runs after footer)
    $(document).ready(function(){
        // Check for saved dark mode preference
        const savedDarkMode = localStorage.getItem('darkMode');
        if (savedDarkMode === 'true') {
            $('body').addClass('dark');
        }
        
        // Remove any existing dark mode event listeners to prevent conflicts
        $(document).off("click", ".dark-mode-link, .light-mode-link");
        
        // Handle dark mode toggle clicks (buyer-specific)
        $(document).on("click", ".dark-mode-link, .light-mode-link", function(e){
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation(); // Prevent other handlers from running
            
            // Toggle dark class
            $('body').toggleClass('dark');
            
            // Save preference to localStorage
            const isDark = $('body').hasClass('dark');
            localStorage.setItem('darkMode', isDark.toString());
        });
    });
    </script>
</body>
</html>
