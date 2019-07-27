<?php

define('APP_DIR', BP . '/app/code');

/**
 * Polyfill for the first key in array - https://www.php.net/manual/en/function.array-key-first.php
 * Can be removed for PHP 7.3
 */
if (!function_exists('array_key_first')) {
    function array_key_first(array $arr) {
        foreach($arr as $key => $unused) {
            return $key;
        }

        return null;
    }
}

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
