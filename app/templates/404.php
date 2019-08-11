<?php /** @var \Awesome\Frontend\Model\App $this */
$staticPath = $this->getStaticPath();
$mediaPath = $this->getMediaPath();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Not Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="<?= $mediaPath; ?>/images/favicon.png"/>
    <link rel="stylesheet" type="text/css" href="<?= $staticPath; ?>/css/errors.css"/>
    <script src="<?= $staticPath; ?>/lib/jquery.min.js"></script>
    <script src="<?= $staticPath; ?>/lib/jquery-ui.min.js"></script>
    <script src="<?= $staticPath; ?>/js/base.js"></script>
</head>
<body class="not-found-page">
    <main class="page-wrapper">
        <?php include('template/header.html'); ?>
        <div class="content">
            <h1>404 error: Not Found</h1>
            <p>Seems, page you are looking for is not present.</p>
            <p>Please start your journey from the <a href="/">Homepage</a></p>
        </div>
        <?php include('template/footer.html'); ?>
    </main>
    <script type="application/javascript">
        jQuery(function () {
            $('body').base({});
        });
    </script>
</body>
</html>
