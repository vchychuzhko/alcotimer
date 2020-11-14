<?php
declare(strict_types=1);

namespace Awesome\Frontend\Helper;

class StaticContentHelper
{
    public const MINIFICATION_FLAG = '.min';

    /**
     * Add minification flag to file path.
     * @param string $path
     * @return void
     */
    public static function addMinificationFlag(string &$path): void
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $path = preg_replace('/\.' . $extension . '$/', self::MINIFICATION_FLAG . '.' . $extension, $path);
    }

    /**
     * Remove minification flag from file path if present.
     * @param string $path
     * @return void
     */
    public static function removeMinificationFlag(string &$path): void
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $path = preg_replace('/' . self::MINIFICATION_FLAG . '\.' . $extension . '$/', '.' . $extension, $path);
    }

    /**
     * Check if minified version of the file exists.
     * @param string $path
     * @return bool
     */
    public static function minifiedVersionExists(string $path): bool
    {
        self::addMinificationFlag($path);

        return file_exists($path) && is_file($path);
    }

    /**
     * Check if file path contains minification flag.
     * @param string $path
     * @return bool
     */
    public static function isFileMinified(string $path): bool
    {
        return strpos(basename($path), self::MINIFICATION_FLAG) !== false;
    }
}