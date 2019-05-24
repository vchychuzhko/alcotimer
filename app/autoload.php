<?php
/**
 * Function to load classes.
 * @param $classNamespace string
 */
function __autoload($classNamespace) {
    $path = 'app';

    foreach (explode('\\', $classNamespace) as $pathItem) {
        $path .= DS . $pathItem;
    }
    require_once(BP . DS . $path . '.php');
}
