<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title><?= __('Forbidden') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="alternate icon" href="/media/favicon/favicon.png" type="image/png"/>
    <link rel="icon" href="/media/favicon/favicon.svg" type="image/svg+xml"/>
    <style>
        <?php include 'css/styles.css' ?>
    </style>
</head>
<body class="forbidden-index-index">
    <?php include 'html/header.php' ?>
    <main>
        <h1>403</h1>
        <p><?= __('The page or file you are trying to access is closed for viewing.') ?></p>
        <p><?= __('Please, try to start your journey from the <a href="/">Homepage</a>') ?></p>
    </main>
    <?php include 'html/footer.php' ?>
</body>
</html>
