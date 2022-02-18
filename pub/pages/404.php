<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title><?= __('Page Not Found') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="alternate icon" href="/media/favicon/favicon.png" type="image/png"/>
    <link rel="icon" href="/media/favicon/favicon.svg" type="image/svg+xml"/>
    <style>
        <?php include 'css/styles.css' ?>
    </style>
</head>
<body class="notfound-index-index">
    <?php include 'html/header.php' ?>
    <main class="page-content error">
        <h1 class="error__title">404</h1>
        <p class="error__info"><?= __('Seems, page you are looking for is not present') ?></p>
        <a class="error__link" href="/"><?= __('Try Homepage') ?></a>
    </main>
    <?php include 'html/footer.php' ?>
</body>
</html>
