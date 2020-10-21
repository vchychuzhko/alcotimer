<?php
declare(strict_types=1);

namespace Awesome\Framework\Helper;

class DataHelper
{
    /**
     * Get element in a multidimensional array by a specified key.
     * Based on https://www.php.net/manual/en/function.array-walk-recursive.php#114574
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
            } elseif (is_array($value)) {
                if ($element = self::arrayGetByKeyRecursive($value, $elementKeyToGet)) {
                    break;
                }
            }
        }

        return $element;
    }

    /**
     * Update element in a multidimensional array by a specified key.
     * Based on https://www.php.net/manual/en/function.array-walk-recursive.php#114574
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
     * Based on https://www.php.net/manual/en/function.array-walk-recursive.php#114574
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
     * Converts camelCase to snake_case.
     * @param string $string
     * @return string
     */
    public static function underscore(string $string): string
    {
        return strtolower(trim(preg_replace('/([A-Z]|[0-9]+)/', "_$1", $string), '_'));
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