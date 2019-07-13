<?php

define('DS', DIRECTORY_SEPARATOR);
define('BP', __DIR__);
define('APP_DIR', BP . DS . 'app' . DS . 'code');
require_once(BP . DS . 'app' . DS . 'autoload.php');

$app = new \Awesome\Base\App();
$app->run();
