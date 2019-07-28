<?php

/**
 * Polyfill for the first key in array - https://www.php.net/manual/en/function.array-key-first.php
 * Can be removed for PHP 7.3
 */
if (!function_exists('array_key_first')) {
    function array_key_first(array $arr)
    {
        foreach($arr as $key => $unused) {
            return $key;
        }

        return null;
    }
}

/**
 * Function for recursive directory removing.
 * @param string $dir
 */
if (!function_exists('rrmdir')) {
    function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);

            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (is_dir($dir . '/' . $object)) {
                        rrmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }

            rmdir($dir);
        }
    }
}
