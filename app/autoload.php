<?php

spl_autoload_register('autoload');

/**
 * Function to load classes by provided class name.
 * @param string $className
 * @throws \Exception
 */
function autoload($className)
{
    $file = APP_DIR . '/' . str_replace('\\', '/', ltrim($className, '\\')) . '.php';

    if (!file_exists($file)) {
        throw new \Exception('File for "'. $className . '" class does not exist.');
    }

    require_once $file;
}
