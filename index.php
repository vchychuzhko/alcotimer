<?php

define('DS', DIRECTORY_SEPARATOR);
define('BP', __DIR__);
define('PUB_DIR', 'pub/');
require_once(BP . '/app/autoload.php');

$app = new \Awesome\Frontend\Model\App();
$app->run();
