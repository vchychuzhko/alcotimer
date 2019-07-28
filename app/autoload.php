<?php

define('APP_DIR', BP . '/app/code');
require_once('polyfill.php');

/**
 * Function to load classes by provided namespace.
 * @param string $classNamespace
 */
function __autoload($classNamespace)
{
    $path = '';

    foreach (explode('\\', $classNamespace) as $pathItem) {
        $path .= '/' . $pathItem;
    }
    require_once(APP_DIR . $path . '.php');
}
