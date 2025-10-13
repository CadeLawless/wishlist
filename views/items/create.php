<?php
// Get wishlist data
$wishlistID = $wishlist['id'];
$wishlistTitle = htmlspecialchars($wishlist['wishlist_name']);
$background_image = $wishlist['background_image'] ?? '';

// Initialize form field variables
$item_name = $item_name ?? '';
$price = $price ?? '';
$quantity = $quantity ?? '1';
$unlimited = $unlimited ?? 'No';
$link = $link ?? '';
$filename = $filename ?? '';
$notes = $notes ?? '';
$priority = $priority ?? '1';
$priority_options = ["1", "2", "3", "4"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/wishlist/images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="/wishlist/css/styles.css" />
    <link rel="stylesheet" type="text/css" href="/wishlist/css/snow.css" />
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
<body class="<?php echo $user['dark'] ? 'dark' : ''; ?>">
    <div id="body">
        <?php include __DIR__ . '/../components/header.php'; ?>
        <div id="container">
            <?php if($background_image != ""){ ?>
                <img class='background-theme desktop-background' src="/wishlist/images/site-images/themes/desktop-backgrounds/<?php echo $background_image; ?>" />
                <img class='background-theme mobile-background' src="/wishlist/images/site-images/themes/mobile-backgrounds/<?php echo $background_image; ?>" />
            <?php } ?>
            <p style="padding-top: 15px;"><a class="button accent" href="/wishlist/<?php echo $wishlistID; ?>">Back to List</a></p>
            <div class="center">
                <div class="wishlist-header center transparent-background">
                    <h1><?php echo $wishlistTitle; ?></h1>
                </div>
            </div>
            <div class="form-container">
                <h2>Add Item</h2>
                <?php if(isset($error_msg)) echo $error_msg?>
                <form method="POST" action="/wishlist/<?php echo $wishlistID; ?>/item" enctype="multipart/form-data">
                    <div class="flex form-flex">
                        <?php
                        $add = true;
                        include __DIR__ . '/_form.php';
                        ?>
                        <p class="large-input center"><input type="submit" class="button text" name="submit_button" value="Add Item"></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
<script src="/wishlist/scripts/autosize-master/autosize-master/dist/autosize.js"></script>
<script src="/wishlist/includes/item-form.js"></script>
