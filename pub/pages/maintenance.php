<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= __('Maintenance'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="shortcut icon" type="image/png" href="/media/images/favicon-maintenance.png"/>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        h1 {
            padding: 20px;
        }

        p {
            padding: 5px 20px;
        }

        footer {
            background: #ffffc2;
            bottom: 0;
            padding: 10px 0;
            position: fixed;
            text-align: center;
            width: 100%;
        }
    </style>
</head>
<body class="maintenance-index-index">
    <main class="page-wrapper">
        <div class="content">
            <h1><?= __('Service is temporary unavailable'); ?></h1>
            <p><?= __('The site is down due to maintenance servicing and it will return to work soon.'); ?></p>
        </div>
    </main>
    <footer>
        <span>&copy; AwesomeTeam. <?= __('All rights reserved'); ?></span>
    </footer>
</body>
</html>
