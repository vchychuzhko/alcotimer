<?php
declare(strict_types=1);

namespace Vch\Framework\Helper;

class DataHelper
{
    /**
     * Get element in a multidimensional array by a specified key.
     * @link https://www.php.net/manual/en/function.array-walk-recursive.php#114574
     * @param array $array
     * @param string $elementKeyToGet
     * @return mixed
     */
    public static function arrayGetByKeyRecursive(array $array, string $elementKeyToGet)
    {
        $element = null;

        foreach ($array as $key => $value) {
            if ($key === $elementKeyToGet) {
                $element = $value;
                break;
            }
            if (is_array($value) && $element = self::arrayGetByKeyRecursive($value, $elementKeyToGet)) {
                break;
            }
        }

        return $element;
    }

    /**
     * Update element in a multidimensional array by a specified key.
     * @link https://www.php.net/manual/en/function.array-walk-recursive.php#114574
     * @param array $array
     * @param string $elementKeyToUpdate
     * @param mixed $newValue
     * @return array
     */
    public static function arrayReplaceByKeyRecursive(array $array, string $elementKeyToUpdate, $newValue): array
    {
        foreach ($array as $key => $value) {
            if ($key === $elementKeyToUpdate) {
                if (is_array($newValue)) {
                    $array[$key] = array_replace_recursive($value, $newValue);
                } else {
                    $array[$key] = $newValue;
                }
            } elseif (is_array($value)) {
                $array[$key] = self::arrayReplaceByKeyRecursive($value, $elementKeyToUpdate, $newValue);
            }
        }

        return $array;
    }

    /**
     * Remove element in a multidimensional array by a specified key.
     * @link https://www.php.net/manual/en/function.array-walk-recursive.php#114574
     * @param array $array
     * @param string $elementKeyToRemove
     * @return array
     */
    public static function arrayRemoveByKeyRecursive(array $array, string $elementKeyToRemove): array
    {
        foreach ($array as $key => $value) {
            if ($key === $elementKeyToRemove) {
                unset($array[$key]);
            } elseif (is_array($value)) {
                $array[$key] = self::arrayRemoveByKeyRecursive($value, $elementKeyToRemove);
            }
        }

        return $array;
    }

    /**
     * Check if array is associative.
     * @link https://stackoverflow.com/a/173479
     * @param array $array
     * @return bool
     */
    public static function isAssociativeArray(array $array): bool
    {
        return $array && array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Check if string is a boolean "true", otherwise return false.
     * Case-insensitive.
     * @param string $string
     * @return bool
     */
    public static function isStringBooleanTrue(string $string): bool
    {
        return strtolower($string) === 'true';
    }

    /**
     * Get max length for items in the provided array.
     * Custom callback can be specified.
     * @param array $array
     * @param callable|null $callback
     * @return int
     */
    public static function getMaxLength(array $array, ?callable $callback = null): int
    {
        $callback = $callback ?: static function ($item) {
            return strlen((string) $item);
        };

        return (int) max(array_map($callback, $array));
    }

    /**
     * Converts camelCase to snake_case.
     * @link https://stackoverflow.com/a/19533226
     * @param string $string
     * @return string
     */
    public static function underscore(string $string): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    /**
     * Converts snake_case or kebab-case to camelCase, depending on provided separator.
     * Underscore separator is used by default.
     * @param string $string
     * @param string $separator
     * @return string
     */
    public static function camelCase(string $string, string $separator = '_'): string
    {
        return lcfirst(self::PascalCase($string, $separator));
    }

    /**
     * Converts snake_case or kebab-case to PascalCase, depending on provided separator.
     * snake_case separator is used by default.
     * @param string $string
     * @param string $separator
     * @return string
     */
    public static function PascalCase(string $string, string $separator = '_'): string
    {
        return str_replace($separator, '', ucwords($string, $separator));
    }
}
