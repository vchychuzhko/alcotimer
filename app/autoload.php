<?php
/**
 * Register autoloader.
 */
if (!is_file(BP . '/vendor/autoload.php')) {
    throw new \Exception('Vendor autoload was not found. Please run "composer install" under application root directory.');
}

require BP . '/vendor/autoload.php';
