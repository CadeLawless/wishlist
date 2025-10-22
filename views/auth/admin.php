<?php
$pageTitle = 'Wish List | Admin Center';
$bodyClass = 'admin-page';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/wishlist/public/images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="/wishlist/public/css/styles.css" />
    <link rel="stylesheet" type="text/css" href="/wishlist/public/css/snow.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <title><?php echo $pageTitle; ?></title>
    <style>
        #container {
            padding: 0 10px 110px;
        }
    </style>
</head>
<body class="<?php echo $bodyClass; ?>">
    <div id="body">
        <?php 
        // Include header without JavaScript (we'll handle it in the page)
        $currentPage = explode("?", $_SERVER["REQUEST_URI"])[0];
        ?>
        <div class="header-container">
            <div class="header">
                <div class="title">
                    <a class="nav-title" href="/wishlist/"><?php require("images/site-images/logo.php"); ?></a>
                    <a href="#" class="dark-mode-link"><?php require("images/site-images/icons/dark-mode.php"); ?></a>
                    <a href="#" class="light-mode-link"><?php require("images/site-images/icons/light-mode.php"); ?></a>
                </div>
                <div class="menu">
                    <?php
                    require("images/site-images/hamburger-menu.php");
                    require("images/site-images/menu-close.php");
                    ?>
                    <div class="menu-links">
                        <a class="nav-link<?php if($currentPage == "/wishlist/" || $currentPage == "/wishlist") echo " active"; ?>" href="/wishlist/">Home<div class="underline"></div></a>
                        <a class="nav-link<?php if($currentPage == "/wishlist/create") echo " active"; ?>" href="/wishlist/create">Create Wishlist<div class="underline"></div></a>
                        <a class="nav-link<?php if($currentPage == "/wishlist/wishlists") echo " active"; ?>" href="/wishlist/wishlists">View Wishlists<div class="underline"></div></a>
                        <div class="nav-link dropdown-link profile-link<?php if(in_array($currentPage, ["/profile", "/admin"])) echo " active-page"; ?>">
                            <div class="outer-link">
                                <span class="profile-icon"><?php require("images/site-images/profile-icon.php"); ?></span>
                                <span>My Account</span>
                                <span class="dropdown-arrow"><?php require("images/site-images/dropdown-arrow.php"); ?></span>
                            </div>
                            <div class="underline"></div>
                            <div class="dropdown-menu hidden">
                                <a class="dropdown-menu-link" href="/wishlist/profile">View Profile</a>
                                <?php if($user['role'] == 'Admin'){ ?>
                                    <a class="dropdown-menu-link" href="/wishlist/admin">Admin Center</a>
                                <?php } ?>
                                <a class="dropdown-menu-link" href="/wishlist/logout">Log Out</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="container">
            <?php include __DIR__ . '/../components/alerts.php'; ?>
            <h1 class="center">Admin Center</h1>
            <div class="sidebar-main">
                <?php include __DIR__ . '/../components/sidebar.php'; ?>
                <div class="content">
                    <h2 style="margin: 0;" class="items-list-title">All Users</h2>
                    <div class="admin-center-table-container">
                        <table class="admin-center-table">
                            <thead>
                                <tr>
                                    <th class="th_border">Name</th>
                                    <th class="th_border">Username</th>
                                    <th class="th_border">Email</th>
                                    <th class="th_border">Role</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $userRow): ?>
                                <tr>
                                    <td data-label="Name"><?php echo htmlspecialchars($userRow['name']); ?></td>
                                    <td data-label="Username"><?php echo htmlspecialchars($userRow['username']); ?></td>
                                    <td data-label="Email">
                                        <?php if(empty($userRow['email'])): ?>
                                            Not set up yet
                                        <?php else: ?>
                                            <a href="mailto:<?php echo htmlspecialchars($userRow['email']); ?>"><?php echo htmlspecialchars($userRow['email']); ?></a>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Role"><?php echo htmlspecialchars($userRow['role']); ?></td>
                                    <td>
                                        <div class="icon-group">
                                            <a class="icon-container" href="/wishlist/admin/wishlists?username=<?php echo urlencode($userRow['username']); ?>">
                                                <?php require("images/site-images/icons/wishlist.php"); ?>
                                            </a>
                                            <a class="icon-container" href="/wishlist/admin/users/edit?username=<?php echo urlencode($userRow['username']); ?>">
                                                <?php require("images/site-images/icons/edit.php"); ?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if($currentPage > 1): ?>
                            <a href="?pageno=<?php echo $currentPage - 1; ?>" class="pagination-link">Previous</a>
                        <?php endif; ?>
                        
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if($i == $currentPage): ?>
                                <span class="pagination-current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?pageno=<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if($currentPage < $totalPages): ?>
                            <a href="?pageno=<?php echo $currentPage + 1; ?>" class="pagination-link">Next</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
<footer>
  <p class="center">&copy; <?php echo date("Y"); ?> Wishlist.<br>
  Designed by Cade and Meleah Lawless. All rights reserved.</p>
</footer>
<script src="/wishlist/public/js/popups.js"></script>
<script>
  $(document).ready(function(){
    // Dark mode functionality
    $(".dark-mode-link, .light-mode-link").on("click", function(e){
      e.preventDefault();
      $(document.body).toggleClass("dark");

      $dark = $(document.body).hasClass("dark") ? "Yes" : "No";
      $.ajax({
            type: "POST",
            url: "/wishlist/toggle-dark-mode",
            data: {
                dark: $dark,
            },
            success: function(response) {
                // Dark mode toggle successful
            },
            error: function(xhr, status, error) {
                console.error('Dark mode toggle failed:', error);
            }
        });
    });

    // Header dropdown functionality
    $(".hamburger-menu").on("click", function(){
        $(this).addClass("hidden");
        $(".close-menu").removeClass("hidden");
        $(".menu-links").css("display", "flex").removeClass("hidden");
    });
    
    $(".close-menu").on("click", function(){
        $(this).addClass("hidden");
        $(".hamburger-menu").removeClass("hidden");
        $(".menu-links").addClass("hidden");
    });
    
    // Dropdown functionality
    $(".dropdown-link").on("click", function(e){
        // If clicking on a dropdown menu link, let it work normally
        if ($(e.target).hasClass('dropdown-menu-link')) {
            return; // Let the link work normally
        }
        
        e.preventDefault();
        e.stopPropagation();
        
        let dropdown_menu = $(this).find(".dropdown-menu");
        if(dropdown_menu.hasClass("hidden")){
            dropdown_menu.removeClass("hidden");
            $(this).addClass("active");
        }else{
            dropdown_menu.addClass("hidden");
            $(this).removeClass("active");
        }
    });
    
    // Close dropdown when clicking outside
    $(document).on("click", function(e){
        if(!$(e.target).closest(".dropdown-link").length){
            $(".dropdown-menu").addClass("hidden");
            $(".dropdown-link").removeClass("active");
        }
    });
  });
</script>
</html>
