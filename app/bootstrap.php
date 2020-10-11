<?php
/**
 * Environment initialization.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
$config = include __DIR__ . '/etc/config.php';
ini_set('display_errors', (bool) ($config['developer_mode'] ?? 0));
unset($config);

if (PHP_VERSION_ID < 70100) { // check for 7.1.0 compatibility
    if (PHP_SAPI === 'cli') {
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
