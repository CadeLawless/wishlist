<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="public/images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="public/css/styles.css" />
    <link rel="stylesheet" type="text/css" href="public/css/snow.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <title><?php echo $title ?? 'Wish List'; ?></title>
    <style>
        h1 {
            margin-top: 0;
        }
        h2 {
            font-size: 28px;
        }
        .form-container {
            margin: clamp(20px, 4vw, 60px) auto 30px;
            background-color: var(--background-darker);
            max-width: 500px;
        }
        input:not([type=submit], #new_password, #current_password) {
            margin-bottom: 0;
        }
        h3 {
            margin-bottom: 0.5em;
        }
        #container {
            padding: 0 10px 110px;
        }
        h1 {
            display: inline-block;
        }
        h2.items-list-title {
            position: relative;
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
            <?php echo $content; ?>
        </div>
    </div>
    <?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
