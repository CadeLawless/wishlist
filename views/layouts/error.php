<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/public/images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="/public/css/styles.css" />
    <title><?php echo htmlspecialchars($title ?? 'Error - Any Wish List'); ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        #body {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        #container {
            max-width: 600px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div id="body">
        <div id="container">
            <?php echo $content; ?>
        </div>
    </div>
</body>
</html>

