<?php

define('DS', DIRECTORY_SEPARATOR);
define('BP', dirname(__DIR__));
define('PUB_DIR', '');
require_once(BP . DS . 'app' . DS . 'autoload.php');

$app = new \Awesome\Frontend\Model\App();
$app->run();
