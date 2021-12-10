<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title><?= __('Maintenance') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="alternate icon" href="/media/favicon/favicon-fix.png" type="image/png"/>
    <link rel="icon" href="/media/favicon/favicon-fix.svg" type="image/svg+xml"/>
    <style>
        <?php include 'css/styles.css' ?>
    </style>
</head>
<body class="maintenance-index-index">
    <?php include 'html/header.php' ?>
    <main>
        <h1>503</h1>
        <p><?= __('Service is temporary unavailable') ?></p>
        <p><?= __('The site is down due to maintenance servicing and will return to work soon.') ?></p>
    </main>
    <?php include 'html/footer.php' ?>
</body>
</html>
