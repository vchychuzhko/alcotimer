<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= __('Forbidden Page'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="shortcut icon" type="image/png" href="../media/images/favicon.png"/>
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
<body class="forbidden-index-index">
    <main class="page-wrapper">
        <div class="content">
            <h1>403: <?= __('Forbidden Page'); ?></h1>
            <p><?= __('The page or file you are trying to access is closed for viewing.'); ?></p>
            <p><?= __('Please, try to start your journey from the <a href="/">Homepage</a>'); ?></p>
        </div>
    </main>
    <footer>
        <span>&copy; AwesomeTeam. <?= __('All rights reserved'); ?></span>
    </footer>
</body>
</html>
