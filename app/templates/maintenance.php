<?php /** @var \Ava\Base\App $this */
$deployedVersion = $this->getDeployedVersion();
$supportEmailAddress = $this->getSupportEmailAddress();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service is temporary unavailable</title>
    <link rel="shortcut icon" type="image/png" href="/pub/media/images/favicon.png"/>
    <link rel="stylesheet" type="text/css" href="/assets/css/errors.css"/>
</head>
<body class="not-found-page">
    <main class="page-wrapper">
        <div class="content">
            <h1>Service is temporary unavailable</h1>
            <p>Site is down due to some service works and it will return to work soon.</p>
            <p>If you have any questions or see this message for too long, please, contact our support:
                <a href="mailto:<?= $supportEmailAddress; ?>?subject = AlcoTimer" class="mail-address">
                    <?= $supportEmailAddress; ?>
                </a>
            </p>
        </div>
        <footer>
            <?php include('template/footer.html'); ?>
        </footer>
    </main>
</body>
</html>
