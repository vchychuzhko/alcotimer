<?php /** @var \Ava\Base\App $this */
$deployedVersion = $this->getDeployedVersion();
$supportEmailAddress = $this->getSupportEmailAddress() ?: 'vlad.chichuzhko@gmail.com';
?>
<!DOCTYPE html>
<html lang="en" style="height: 100%;">
<head>
    <meta charset="UTF-8">
    <title>Service is temporary unavailable</title>
    <link rel="shortcut icon" type="image/png" href="/pub/media/images/favicon.png"/>
    <link rel="stylesheet" type="text/css" href="/assets/css/errors.css"/>
    <script src="/lib/jquery/jquery.min.js"></script>
    <script src="/lib/jquery-ui/jquery-ui.min.js"></script>
    <script src="/assets/js/base.js"></script>
</head>
<body class="not-found-page" style="height: 100%;">
    <main class="page-wrapper" style="display: flex; flex-direction: column; height: 100%;">
        <?php if (file_exists('template/header.html')): ?>
            <?php include('template/header.html'); ?>
        <?php else: ?>
            <header style="background: #fff; height: 50px; text-align: center; position: relative; width: 100%;">
                <div class="logo-container"
                     style="height: 44px; left: 50%; padding: 3px 0; position: absolute; transform: translateX(-50%);">
                    <img src="/pub/media/images/logo.png" alt="AlcoTimer logo" style="height: 100%;"/>
                </div>
            </header>
        <?php endif; ?>
        <div class="content" style="flex: 1 0 auto;">
            <h1 style="padding: 20px;">Service is temporary unavailable</h1>
            <p style="padding: 5px 20px;">Site is down due to some service works and it will return to work soon.</p>
            <p style="padding: 5px 20px;">If you have any questions or see this message for too long, please, contact our support:
                <a href="mailto:<?= $supportEmailAddress; ?>?subject=AlcoTimer"
                   class="mail-address word-break copy-on-click"
                   style="color: inherit; word-wrap: break-word;">
                    <?= $supportEmailAddress; ?>
                </a>
            </p>
        </div>
        <?php if (file_exists('template/footer.html')): ?>
            <?php include('template/footer.html'); ?>
        <?php else: ?>
            <footer style="background: #ffffc2; flex-shrink: 0; text-align: center; padding: 10px 0;">
                <p>&copy; 2019 AlcoTimer. All rights reserved</p>
            </footer>
        <?php endif; ?>
    </main>
    <script type="application/javascript">
        jQuery(function () {
            $('body').base({});
        });
    </script>
</body>
</html>
