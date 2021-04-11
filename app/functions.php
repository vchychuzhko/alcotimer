<?php
declare(strict_types=1);

if (!function_exists('get_class_name')) {
    /**
     * Get class name with no namespace.
     * @link https://www.php.net/manual/en/function.get-class.php#114568
     * @param object $object
     * @return string
     */
    function get_class_name($object): string
    {
        $objectName = get_class($object);

        if ($pos = strrpos($objectName, '\\')) {
            $objectName = substr($objectName, $pos + 1);
        }

        return $objectName;
    }
}

if (!function_exists('in_array_r')) {
    /**
     * Check if value exists in a multidimensional array.
     * @link https://stackoverflow.com/a/4128377
     * @param mixed $needle
     * @param array $haystack
     * @param bool $strict
     * @return bool
     */
    function in_array_r($needle, array $haystack, bool $strict = false): bool
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
     * @link https://stackoverflow.com/a/2606638
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    function str_replace_first(string $search, string $replace, string $subject): string
    {
        $pos = strpos($subject, $search);

        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }
}

if (!function_exists('array_export')) {
    /**
     * PHP var_export() modification with short array syntax (square brackets) indented 4 spaces.
     * @link https://www.php.net/manual/en/function.var-export.php#124194
     * @param array $array
     * @param bool $return
     * @return string|void
     */
    function array_export(array $array, bool $return = false)
    {
        $export = var_export($array, true);
        $patterns = [
            "/^([ ]*)(.*)/m"                   => '$1$1$2',
            "/array \(/"                       => '[',
            "/^([ ]*)\)(,?)$/m"                => '$1]$2',
            "/=>[ ]?\n[ ]+\[/"                 => '=> [',
            "/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
        ];
        $export = preg_replace(array_keys($patterns), array_values($patterns), $export);

        if ($return) {
            return $export;
        }

        echo $export;
    }
}

if (!function_exists('__')) {
    /**
     * Translate given phrase with replacement arguments.
     * If second parameter is an array, it will be used as substitution map.
     * @param string $phrase
     * @param mixed ...$args
     * @return \Awesome\Framework\Model\Phrase
     */
    function __(string $phrase, ...$args): \Awesome\Framework\Model\Phrase
    {
        if (!empty($args) && is_array($args[0])) {
            $args = $args[0];
        }

        return new \Awesome\Framework\Model\Phrase($phrase, $args);
    }
}
