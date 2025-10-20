<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <link rel="stylesheet" type="text/css" href="css/snow.css" />
    <title><?php echo $title ?? 'Wish List'; ?></title>
</head>
<body class="<?php echo isset($user['dark']) && $user['dark'] === 'Yes' ? 'dark' : ''; ?>">
    <div id="body">
        <div id="container">
            <?php echo $content; ?>
        </div>
    </div>
    <?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
