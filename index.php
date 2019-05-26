<?php

define('DS', DIRECTORY_SEPARATOR);
define('BP', dirname(__FILE__));
define('APP_DIR', BP . DS. 'app');
require_once(APP_DIR . DS . 'autoload.php');

try {
    $app = new \Ava\Base\App();
    $app->run();
} catch (\Throwable $t) {
    $logger = new \Ava\Logger\LogWriter();
    $logger->write($t->getMessage() . "\n" . $t->getTraceAsString());
}
