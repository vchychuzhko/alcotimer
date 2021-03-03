<?php
/**
 * Register autoloader.
 */
$vendorAutoload = BP . '/vendor/autoload.php';

if (!is_file($vendorAutoload)) {
    throw new \Exception('Vendor autoload was not found. Please run "composer install" under application root directory.');
}

require $vendorAutoload;
