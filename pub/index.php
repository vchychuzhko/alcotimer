<?php

define('DS', DIRECTORY_SEPARATOR);
define('BP', dirname(__DIR__));
define('PUB_DIR', '');
require_once(BP . '/app/autoload.php');

$app = new \Awesome\Base\Model\App();
$app->run();
