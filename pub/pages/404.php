<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= __('Page Not Found'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="shortcut icon" type="image/png" href="/media/images/favicon.png"/>
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

        a {
            color: inherit;
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
<body class="notfound-index-index">
    <main class="page-wrapper">
        <div class="content">
            <h1>404: <?= __('Page Not Found'); ?></h1>
            <p><?= __('Seems, page you are looking for is not present.'); ?></p>
            <p><?= __('Please, try to start your journey from the <a href="/">Homepage</a>'); ?></p>
        </div>
    </main>
    <footer>
        <span>&copy; AwesomeTeam. <?= __('All rights reserved'); ?></span>
    </footer>
</body>
</html>
