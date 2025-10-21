<?php
// Buyer view for public wishlist access - matches original structure
$wishlistID = $wishlist['id'];
$wishlist_name_input = $wishlist['wishlist_name'];
$wishlist_name = htmlspecialchars($wishlist_name_input);
$wishlistTitle = $wishlist_name;
$year = $wishlist['year'];
$type = $wishlist['type'];
$duplicate = $wishlist['duplicate'] == 0 ? "" : " ({$wishlist['duplicate']})";
$secret_key = $wishlist['secret_key'];
$theme_background_id = $wishlist['theme_background_id'];
$theme_gift_wrap_id = $wishlist['theme_gift_wrap_id'];

// Get background image if theme is set
$background_image = \App\Services\ThemeService::getBackgroundImage($theme_background_id);

// Get user name from username
$username = $wishlist['username'];
$stmt = \App\Core\Database::query("SELECT name FROM wishlist_users WHERE username = ?", [$username]);
$name_result = $stmt->get_result()->fetch_assoc();
$name = $name_result ? htmlspecialchars($name_result['name']) : $username;

// Initialize sort variables (like original)
$sort_priority = $_SESSION['buyer_sort_priority'] ?? "";
$sort_price = $_SESSION['buyer_sort_price'] ?? "";
$_SESSION['buyer_sort_priority'] = $sort_priority;
$_SESSION['buyer_sort_price'] = $sort_price;

// Build SQL order clause based on filters (like original)
$priority_order = $sort_priority ? "priority ASC, " : "";
$price_order = $sort_price ? "price * 1 ASC, " : "";
?>

<?php if($background_image): ?>
    <img class='background-theme desktop-background' src="/wishlist/public/images/site-images/themes/desktop-backgrounds/<?php echo htmlspecialchars($background_image); ?>" />
    <img class='background-theme mobile-background' src="/wishlist/public/images/site-images/themes/mobile-backgrounds/<?php echo htmlspecialchars($background_image); ?>" />
<?php endif; ?>

<div class="center">
    <div class="wishlist-header center transparent-background">
        <h1><?php echo $wishlistTitle; ?></h1>
    </div>
</div>

<div class='items-list-container'>
    <h2 class="transparent-background items-list-title" id='paginate-top' class='center'>All Items</h2>
    
    <?php if(!empty($items)): ?>
                <!-- Top Pagination controls -->
                <?php include __DIR__ . '/../components/pagination-controls.php'; ?>
        
        <!-- Sort and Filter Form (like original) -->
        <form class="filter-form center" method="POST" action="">
            <div class="filter-inputs">
                <div class="filter-input">
                    <label for="sort-priority">Sort by Priority</label><br>
                    <select class="select-filter" id="sort-priority" name="sort_priority">
                        <option value="">None</option>
                        <option value="1" <?php if($sort_priority == "1") echo "selected"; ?>>Highest to Lowest</option>
                        <option value="2" <?php if($sort_priority == "2") echo "selected"; ?>>Lowest to Highest</option>
                    </select>
                </div>
                <div class="filter-input">
                    <label for="sort-price">Sort by Price</label><br>
                    <select class="select-filter" id="sort-price" name="sort_price">
                        <option value="">None</option>
                        <option value="1" <?php if($sort_price == "1") echo "selected"; ?>>Lowest to Highest</option>
                        <option value="2" <?php if($sort_price == "2") echo "selected"; ?>>Highest to Lowest</option>
                    </select>
                </div>
            </div>
        </form>
    <?php endif; ?>
    
    <?php if(empty($items)): ?>
        <p>No items in this wishlist yet.</p>
    <?php else: ?>
        <div class='items-list-sub-container'>
            <div class="items-list main">
                <?php foreach($items as $item): ?>
                    <?php echo \App\Services\ItemRenderService::renderItem($item, $wishlistID, $pageno, 'buyer'); ?>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Pagination controls -->
        <?php include __DIR__ . '/../components/pagination-controls.php'; ?>
        
        <div class="count-showing">Showing <?php echo (($pageno - 1) * 12) + 1; ?>-<?php echo min($pageno * 12, count($all_items)); ?> of <?php echo count($all_items); ?> items</div>
    <?php endif; ?>
</div>

<!-- Image popup container (global, reused for all items) -->
<div class='popup-container image-popup-container hidden'>
    <div class='popup image-popup'>
        <div class='close-container transparent-background'>
            <a href='#' class='close-button'>
                <?php require(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'site-images' . DIRECTORY_SEPARATOR . 'menu-close.php'); ?>
            </a>
        </div>
        <img class='popup-image' src='' alt='wishlist item image'>
    </div>
</div>

<script>$type = "buyer";</script>
<script>
    $(document).ready(function(){
        // confetti on mark as purchased
        $(document.body).on("click", ".purchase-button", function(e){
            e.preventDefault();
            let button = this;
            let item_id = button.id.split("-")[1];
            setTimeout(function(){
                let pageno_url = (document.querySelector(".page-number")) ? "&pageno="+document.querySelector(".page-number").textContent : "";
                window.location = "/wishlist/buyer/<?php echo $wishlist['secret_key']; ?>/purchase/" + item_id + pageno_url;
            }, 3000);
            button.style.pointerEvents = "none";
            var windowWidth = window.innerWidth;
            var windowHeight = window.innerHeight;
            let position = button.getBoundingClientRect();
            let left = position.left;
            let top = position.top;
            let centerX = left + button.offsetWidth / 2;
            centerX = centerX / windowWidth * 100;
            let centerY = top + button.offsetHeight / 2;
            centerY = centerY / windowHeight * 100;
            console.log(centerX, centerY);
            button.style.backgroundColor = "var(--accent)";
            confetti("tsparticles", {
                angle: 90,
                count: 200,
                position: {
                    x: centerX,
                    y: centerY,
                },
                spread: 60,
                startVelocity: 45,
                decay: 0.9,
                gravity: 1,
                drift: 0,
                ticks: 200,
                shapes: ["image"],
                shapeOptions: {
                    image: [
                        {
                            src: '/wishlist/images/site-images/confetti/christmas-confetti-1.png',
                            width: 100,
                            height: 100,
                        },
                        {
                            src: '/wishlist/images/site-images/confetti/christmas-confetti-2.png',
                            width: 100,
                            height: 100,
                        },
                        {
                            src: '/wishlist/images/site-images/confetti/christmas-confetti-3.png',
                            width: 100,
                            height: 100,
                        },
                        {
                            src: '/wishlist/images/site-images/confetti/christmas-confetti-4.png',
                            width: 100,
                            height: 100,
                        },
                        {
                            src: '/wishlist/images/site-images/confetti/christmas-confetti-5.png',
                            width: 100,
                            height: 100,
                        },
                        {
                            src: '/wishlist/images/site-images/confetti/christmas-confetti-6.png',
                            width: 100,
                            height: 100,
                        }
                    ],
                },
                scalar: 3,
                zIndex: 1002,
                disableForReducedMotion: true,
            });
        });
    });
</script>
