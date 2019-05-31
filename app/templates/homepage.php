<?php /** @var \Ava\Base\App $this */
$deployedVersion = $this->getDeployedVersion();
$supportEmailAddress = $this->getSupportEmailAddress();
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
    <script src="/assets/js/menu.js"></script>
    <script src="/assets/js/range-slider.js"></script>
    <script src="/assets/js/radial-slider.js"></script>
    <script src="/assets/js/settings.js"></script>
    <script src="/assets/js/timer.js"></script>
    <script src="/assets/js/message.js"></script>
</head>
<body class="homepage">
    <div class="menu">
        <div class="toggle-container">
            <button class="toggle" type="button" name="Menu toggler" value="Toggle Menu"></button>
        </div>
        <div class="menu-list">
            <div class="title">
                <h2>AlcoMenu</h2>
            </div>
            <div class="settings">
                <ul>
                    <li>
                        <div class="random-time range-slider">
                            <h3>Set time range (mins):</h3>
                            <div class="range-controls">
                                <input type="range" min="0" max="60" value="5" class="min-range time" name="min-time" id="min-time">
                                <input type="range" min="0" max="60" value="20" class="max-range time" name="max-time" id="max-time">
                                <label for="min-time" style="display: none">Minimal random time value</label>
                                <label for="max-time" style="display: none">Maximum random time value</label>
                            </div>
                            <div class="range-inputs">
                                <input type="number" value="5" class="min-value time" name="min-time-input" id="min-time-input">
                                <label for="min-time-input" style="display: none">Minimum random time input value</label>
                                <span class="input-separator">- to -</span>
                                <input type="number" value="20" class="max-value time" name="max-time-input" id="max-time-input">
                                <label for="max-time-input" style="display: none">Maximum random time input value</label>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="random-time checkbox">
                            <input type="checkbox" class="show-random-time" name="show-random-time" id="show-random-time">
                            <label for="show-random-time">Show random time</label>
                        </div>
                    </li>
                    <li>
                        <div class="loader-settings checkbox">
                            <input type="checkbox" class="show-loader" name="show-loader" id="show-loader" checked>
                            <label for="show-loader">Show loader on run</label>
                        </div>
                    </li>
                </ul>
                <div class="settings-buttons-container">
                    <button class="reset-button" type="button" name="settings-reset" value="Reset  settings">Reset</button>
                    <button class="save-button" type="button" name="settings-save" value="Save Settings">Save</button>
                </div>
            </div>
            <div class="contact-us">
                <p>Face any bug or have new ideas?</p>
                <p>Send us an email:
                    <a href="mailto:<?= $supportEmailAddress; ?>?subject = AlcoTimer" class="mail-address">
                        <?= $supportEmailAddress; ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
    <main class="page-wrapper">
        <div class="mobile-overlay"></div>
        <div class="content">
            <header>
                <div class="logo-container">
                    <img src="/pub/media/images/logo.png" alt="AlcoTimer logo"/>
                </div>
            </header>
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
        <footer>
            <?php include('template/footer.html'); ?>
        </footer>
    </main>
    <script type="application/javascript">
        jQuery(function () {
            $('.menu').menu({});
            $('.random-time.range-slider').rangeSlider({});
            $('.settings').settings({});
            $('.timer-container').timer({});
            $('.radial-container').radialSlider({});
        });
    </script>
</body>
</html>
