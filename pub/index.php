<?php

define('PUB_DIR', '');
require_once(__DIR__ . '/../app/autoload.php');

$app = new \Awesome\Base\Model\App();
$app->run();
