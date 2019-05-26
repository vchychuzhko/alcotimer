<?php

define('DS', DIRECTORY_SEPARATOR);
define('BP', dirname(__FILE__));
define('APP_DIR', BP . DS . 'app' . DS . 'code');
require_once(APP_DIR . DS . 'autoload.php');

$app = new \Ava\Base\App();
$app->run();
