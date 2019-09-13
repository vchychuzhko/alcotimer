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
 * Function for recursive directory removing - https://www.php.net/manual/en/function.rmdir.php#117354
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

/**
 * Get all files in the directory recursively by regex filter if needed.
 * https://stackoverflow.com/a/35105800
 * @param string $dir
 * @param string $filter
 * @param array $results
 * @return array
 */
if (!function_exists('rscandir')) {
    function rscandir($directory, $filter = '', &$results = [])
    {
        foreach (scandir($directory) as $object) {
            $path = realpath($directory . '/' . $object);

            if (!is_dir($path)) {
                if (empty($filter) || preg_match($filter, $path)) {
                    $results[] = $path;
                }
            } elseif ($object !== '.' && $object !== '..') {
                rscandir($path, $filter, $results);
            }
        }

        return $results;
    }
}

/**
 * Replace the first occurrence of the searched string.
 * https://stackoverflow.com/a/2606638
 * @param string $search
 * @param string $replace
 * @param string $string
 * @return string
 */
if (!function_exists('str_replace_first')) {
    function str_replace_first($search, $replace, $string)
    {
        $pos = strpos($string, $search);

        if ($pos !== false) {
            return substr_replace($string, $replace, $pos, strlen($search));
        }

        return $string;
    }
}
