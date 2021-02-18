<?php
/**
 * Register autoloader.
 */
$vendorAutoload = BP . '/vendor/autoload.php';

if (!is_file($vendorAutoload)) {
    throw new \Exception('Vendor autoload.php file was not found and cannot be loaded');
}

require $vendorAutoload;
