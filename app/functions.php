<?php
/**
 * Get first key in array.
 * Based on https://www.php.net/manual/en/function.array-key-first.php
 * A polyfill for PHP versions below 7.3
 * @param array $array
 * @return mixed
 */
if (!function_exists('array_key_first')) {
    function array_key_first(array $array)
    {
        foreach($array as $key => $unused) {
            return $key;
        }

        return null;
    }
}

/**
 * Update element in a multidimensional array by a specified key.
 * Based on https://www.php.net/manual/en/function.array-walk-recursive.php#114574
 * @param array $array
 * @param string $elementKeyToRemove
 */
if (!function_exists('array_update_by_key_recursive')) {
    function array_update_by_key_recursive(&$array, $elementKeyToUpdate, $newValue)
    {
        foreach ($array as $key => $value) {
            if ($key === $elementKeyToUpdate) {
                $array[$key] = array_replace_recursive($array[$key], $newValue);
            } elseif (is_array($value)) {
                array_update_by_key_recursive($array[$key], $elementKeyToUpdate, $newValue);
            }
        }
    }
}

/**
 * Remove element in a multidimensional array by a specified key.
 * Based on https://www.php.net/manual/en/function.array-walk-recursive.php#114574
 * @param array $array
 * @param string $elementKeyToRemove
 */
if (!function_exists('array_remove_by_key_recursive')) {
    function array_remove_by_key_recursive(&$array, $elementKeyToRemove)
    {
        foreach ($array as $key => $value) {
            if ($key === $elementKeyToRemove) {
                unset($array[$key]);
            } elseif (is_array($value)) {
                array_remove_by_key_recursive($array[$key], $elementKeyToRemove);
            }
        }
    }
}

/**
 * Remove element in a multidimensional array by a specified value.
 * Based on https://www.php.net/manual/en/function.array-walk-recursive.php#114574
 * @param array $array
 * @param string $elementValueToRemove
 */
if (!function_exists('array_remove_by_value_recursive')) {
    function array_remove_by_value_recursive(&$array, $elementValueToRemove)
    {
        foreach ($array as $key => $value) {
            if ($value === $elementValueToRemove) {
                unset($array[$key]);
            } elseif (is_array($value)) {
                array_remove_by_value_recursive($array[$key], $elementValueToRemove);
            }
        }
    }
}

/**
 * Remove directory recursively.
 * Based on https://www.php.net/manual/en/function.rmdir.php#117354
 * @param string $dir
 */
if (!function_exists('rrmdir')) {
    function rrmdir($directory)
    {
        if (is_dir($directory)) {
            $objects = scandir($directory);

            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (is_dir($directory . '/' . $object)) {
                        rrmdir($directory . '/' . $object);
                    } else {
                        unlink($directory . '/' . $object);
                    }
                }
            }

            rmdir($directory);
        }
    }
}

/**
 * Get all files in the directory recursively by regex filter if needed.
 * Based on https://stackoverflow.com/a/35105800
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
 * Based on https://stackoverflow.com/a/2606638
 * @param string $search
 * @param string $replace
 * @param string $subject
 * @return string
 */
if (!function_exists('str_replace_first')) {
    function str_replace_first($search, $replace, $subject)
    {
        $pos = strpos($subject, $search);

        if ($pos !== false) {
            return substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }
}
