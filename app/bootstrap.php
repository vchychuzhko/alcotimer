<?php
/**
 * Environment initialization.
 */
if (PHP_MAJOR_VERSION < 7 && PHP_MINOR_VERSION < 1) {
    if (PHP_SAPI == 'cli') {
        echo 'Application requires PHP 7.1 or later.';
    } else {
        echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <p>Application requires PHP 7.1 or later.</p>
</div>
HTML;
    }
    exit(1);
}

define('DS', DIRECTORY_SEPARATOR);
define('BP', str_replace(DS, '/', dirname(__DIR__)));
define('APP_DIR', BP . '/app/code');

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/functions.php';
