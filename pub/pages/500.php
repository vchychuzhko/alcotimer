<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title><?= __('Internal error') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="alternate icon" href="/media/favicon/favicon-fix.png" type="image/png"/>
    <link rel="icon" href="/media/favicon/favicon-fix.svg" type="image/svg+xml"/>
    <style>
        <?php include 'css/styles.css' ?>
    </style>
</head>
<body class="internal_error-index-index">
    <?php include 'html/header.php' ?>
    <main>
        <h1>500</h1>
        <p><?= __('An internal error occurred during loading this page') ?></p>
        <p><?= __('Details are hidden due to security reasons and can be found in log files.') ?></p>
    </main>
    <?php include 'html/footer.php' ?>
</body>
</html>
