<?php

define('PUB_DIR', 'pub/');
require_once(__DIR__ . '/app/autoload.php');

//@TODO: implement error handle including logging
//function errorHandler($foo, $bar, $baz) // -- 4
//{
//    $foo = false;
//}
//set_error_handler('errorHandler');

//try {
$app = new \Awesome\Base\Model\App();
$app->run();
//} catch (Throwable $t) {
//    echo 'Error occurred: ' . $t->getMessage();
//    exit(1);
//}
