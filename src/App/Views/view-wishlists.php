<?php require(__DIR__ . "/includes/header.php"); ?>
<?php require(__DIR__ . "/includes/nav.php"); ?>

<div id="container">
    <h1 class="center"><?= $user->name; ?>'s Wish Lists</h1>
    <p class="center" style="margin: 0 0 36px;"><a class="button primary" href="/wishlist/create-wishlist">Create a New Wish List</a></p>
    <div class="wishlist-grid">
        <?php
        $wishList->printWishLists($user);
        ?>
    </div>
</div>

<?php require(__DIR__ . "/includes/footer.php"); ?>