<?php

define('APP_DIR', BP . DS . 'app' . DS . 'code');

/**
 * Function to load classes.
 * @param string $classNamespace
 */
function __autoload($classNamespace) {
    $path = '';

    foreach (explode('\\', $classNamespace) as $pathItem) {
        $path .= DS . $pathItem;
    }
    require_once(APP_DIR . $path . '.php');
}
