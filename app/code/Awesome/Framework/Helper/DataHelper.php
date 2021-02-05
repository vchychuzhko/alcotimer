<?php
declare(strict_types=1);

namespace Awesome\Framework\Helper;

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
     * @return void
     */
    public static function arrayReplaceByKeyRecursive(array &$array, string $elementKeyToUpdate, $newValue): void
    {
        foreach ($array as $key => $value) {
            if ($key === $elementKeyToUpdate) {
                if (is_array($newValue)) {
                    $array[$key] = array_replace_recursive($array[$key], $newValue);
                } else {
                    $array[$key] = $newValue;
                }
            } elseif (is_array($value)) {
                self::arrayReplaceByKeyRecursive($array[$key], $elementKeyToUpdate, $newValue);
            }
        }
    }

    /**
     * Remove element in a multidimensional array by a specified key.
     * @link https://www.php.net/manual/en/function.array-walk-recursive.php#114574
     * @param array $array
     * @param string $elementKeyToRemove
     * @return void
     */
    public static function arrayRemoveByKeyRecursive(array &$array, string $elementKeyToRemove): void
    {
        foreach ($array as $key => $value) {
            if ($key === $elementKeyToRemove) {
                unset($array[$key]);
            } elseif (is_array($value)) {
                self::arrayRemoveByKeyRecursive($array[$key], $elementKeyToRemove);
            }
        }
    }

    /**
     * Check if string is a boolean "true", otherwise return false.
     * Case insensitive.
     * @param string $string
     * @return bool
     */
    public static function isStringBooleanTrue(string $string): bool
    {
        return strtolower($string) === 'true';
    }

    /**
     * Cast value to a corresponding type.
     * String like "true" or "false" are treated as bool type, not case sensitive.
     * @param mixed $value
     * @return mixed
     */
    public static function castValue($value)
    {
        if (is_numeric($value)) {
            $value += 0;
        } elseif (is_string($value) && in_array(strtolower($value), ['true', 'false'], true)) {
            $value = self::isStringBooleanTrue($value);
        }

        return $value;
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
     * Converts snake_case to camelCase.
     * @param string $string
     * @param string $separator
     * @return string
     */
    public static function camelCase(string $string, string $separator = '_'): string
    {
        return str_replace($separator, '', lcfirst(ucwords($string, $separator)));
    }
}