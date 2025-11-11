<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/public/images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="/public/css/styles.css" />
    <link rel="stylesheet" type="text/css" href="/public/css/snow.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <title><?php echo $title ?? 'Wish List'; ?></title>
    <?php if(isset($customStyles) && !empty($customStyles)): ?>
    <style>
    <?php echo $customStyles; ?>
    </style>
    <?php endif; ?>
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
