<?php
declare(strict_types=1);

namespace Awesome\Frontend\Helper;

class StaticContentHelper
{
    public const MINIFICATION_FLAG = '.min';

    /**
     * Add minification flag to file path.
     * Do nothing if already present.
     * @param string $path
     * @return string
     */
    public static function addMinificationFlag(string $path): string
    {
        return preg_replace('/^(.*?)(' . self::MINIFICATION_FLAG . ')?(\.\w+)?$/i', '$1' . self::MINIFICATION_FLAG . '$3', $path);
    }

    /**
     * Remove minification flag from file path if present.
     * @param string $path
     * @return string
     */
    public static function removeMinificationFlag(string $path): string
    {
        return preg_replace('/' . self::MINIFICATION_FLAG . '(\.\w+)?$/i', '$1', $path);
    }

    /**
     * Check if minified version of the file exists.
     * @param string $path
     * @return bool
     */
    public static function minifiedVersionExists(string $path): bool
    {
        $path = self::addMinificationFlag($path);

        return file_exists($path) && is_file($path);
    }

    /**
     * Check if file path contains minification flag.
     * @param string $path
     * @return bool
     */
    public static function isFileMinified(string $path): bool
    {
        return (bool) preg_match('/' . self::MINIFICATION_FLAG . '(\.\w+)?$/i', $path);
    }
}