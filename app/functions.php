<?php

if (!function_exists('array_key_first')) {
    /**
     * Get first key in array.
     * Based on https://www.php.net/manual/en/function.array-key-first.php
     * A polyfill for PHP versions below 7.3
     * @param array $array
     * @return mixed
     */
    function array_key_first($array)
    {
        foreach ($array as $key => $unused) {
            return $key;
        }

        return null;
    }
}

if (!function_exists('in_array_r')) {
    /**
     * Check if value exists in a multidimensional array.
     * Based on https://stackoverflow.com/a/4128377
     * @param mixed $needle
     * @param array $haystack
     * @param bool $strict
     * @return mixed
     */
    function in_array_r($needle, $haystack, $strict = false)
    {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle)
                || (is_array($item) && in_array_r($needle, $item, $strict))
            ) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('str_replace_first')) {
    /**
     * Replace the first occurrence of the searched string.
     * Based on https://stackoverflow.com/a/2606638
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    function str_replace_first($search, $replace, $subject)
    {
        $pos = strpos($subject, $search);

        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }
}
