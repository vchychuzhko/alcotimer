<?php

define('APP_DIR', BP . '/app/code');

/**
 * Function to load classes.
 * @param string $classNamespace
 */
function __autoload($classNamespace) {
    $path = '';

    foreach (explode('\\', $classNamespace) as $pathItem) {
        $path .= '/' . $pathItem;
    }
    require_once(APP_DIR . $path . '.php');
}
