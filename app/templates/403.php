<?php /** @var \Ava\Base\App $this */
$deployedVersion = $this->getDeployedVersion();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forbidden</title>
    <link rel="shortcut icon" type="image/png" href="/pub/media/images/favicon.png"/>
    <link rel="stylesheet" type="text/css" href="/assets/css/errors.css"/>
</head>
<body class="forbidden-page">
    <main class="page-wrapper">
        <div class="content">
            <h1>403 error: Forbidden</h1>
            <p>The page you are trying to access is not accessible.</p>
            <p>Please start your journey from the <a href="/">Homepage</a></p>
        </div>
        <footer>
            <?php include('template/footer.html'); ?>
        </footer>
    </main>
</body>
</html>

