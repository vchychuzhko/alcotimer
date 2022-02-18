<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title><?= __('Unauthorized') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="alternate icon" href="/media/favicon/favicon.png" type="image/png"/>
    <link rel="icon" href="/media/favicon/favicon.svg" type="image/svg+xml"/>
    <style>
        <?php include 'css/styles.css' ?>
    </style>
</head>
<body class="unauthorized-index-index">
    <?php include 'html/header.php' ?>
    <main class="page-content error">
        <h1 class="error__title">401</h1>
        <p class="error__info"><?= __("Request's authorization was not correct") ?></p>
        <p class="error__info"><?= __('Please, check it and try to reload the page.') ?></p>
    </main>
    <?php include 'html/footer.php' ?>
</body>
</html>
