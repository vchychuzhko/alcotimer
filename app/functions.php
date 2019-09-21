<?php
/**
 * Get first key in array.
 * https://www.php.net/manual/en/function.array-key-first.php
 * Can be removed for PHP 7.3
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
 * Remove directory recursively.
 * https://www.php.net/manual/en/function.rmdir.php#117354
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
