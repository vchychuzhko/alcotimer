<?php

define('DS', DIRECTORY_SEPARATOR);
define('BP', __DIR__);
define('PUB_DIR', 'pub/');
require_once(BP . '/app/autoload.php');

//@TODO: implement error handle
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
