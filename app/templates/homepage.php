<?php /** @var \Ava\Base\App $this */
$deployedVersion = $this->getDeployedVersion();
$supportEmailAddress = $this->getSupportEmailAddress();
$timerConfigurations = $this->getTimerConfigurations();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AlcoTimer</title>
    <meta name="description" content="Web App for people, who would like to make drinking process become really challenging">
    <meta name="keywords" content="Alcohol,Alco,Timer,AlcoTimer,Web,App,Drink">
    <link rel="shortcut icon" type="image/png" href="/pub/media/images/favicon.png"/>
    <link rel="stylesheet" type="text/css" href="/assets/css/homepage.css"/>
    <script src="/lib/jquery/jquery.min.js"></script>
    <script src="/lib/jquery-ui/jquery-ui.min.js"></script>
    <script src="/assets/js/base.js"></script>
    <script src="/assets/js/range-slider.js"></script>
    <script src="/assets/js/radial-slider.js"></script>
    <script src="/assets/js/settings.js"></script>
    <script src="/assets/js/timer.js"></script>
</head>
<body class="homepage">
    <div class="menu">
        <div class="toggle-container">
            <button class="toggle" type="button" name="Menu toggler" value="Toggle Menu"></button>
        </div>
        <div class="menu-list">
            <?php include('template/settings.html'); ?>
            <div class="contact-us">
                <p>Face any bug or have new ideas?</p>
                <p>Send us an email:
                    <a href="mailto:<?= $supportEmailAddress; ?>?subject = AlcoTimer" class="mail-address word-break copy-on-click">
                        <?= $supportEmailAddress; ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
    <main class="page-wrapper">
        <div class="mobile-overlay"></div>
        <div class="content">
            <?php include('template/header.html'); ?>
            <div class="timer-container">
                <div class="timer-wrapper radial-container">
                    <div class="timer-button-container">
                        <button class="timer-button" type="button" name="start-stop" value="Start/Stop">
                            <span class="time-value">15:30</span>
                            <span class="timer-button-title" style="display: block; bottom: 0; position: absolute; left: 50%; transform: translateX(-50%)"></span>
                        </button>
                    </div>
                    <div class="radial-slider">
                        <div class="dot-container">
                            <div class="dot"></div>
                        </div>
                    </div>
                    <div class="loader">
                        <div class="vjs-loading-spinner"></div>
                    </div>
                </div>
                <div class="random-button-container">
                    <button class="random-button" type="button" name="random-button" value="Set random time">
                        <span class="random-icon"></span>
                        <span class="random-button-title">Random</span>
                    </button>
                </div>
            </div>
        </div>
        <?php include('template/footer.html'); ?>
    </main>
    <script type="application/javascript">
        jQuery(function () {
            $('body').base({});
            $('.random-time.range-slider').rangeSlider({
                difference: <?= $timerConfigurations['difference']; ?>,
                minValue: <?= $timerConfigurations['min_value']; ?>,
                maxValue: <?= $timerConfigurations['max_value']; ?>
            });
            $('.settings').settings({
                minDefaultValue: <?= $timerConfigurations['default_min_value']; ?>,
                maxDefaultValue: <?= $timerConfigurations['default_max_value']; ?>,
            });
            $('.timer-container').timer({});
            $('.radial-container').radialSlider({});
        });
    </script>
</body>
</html>
