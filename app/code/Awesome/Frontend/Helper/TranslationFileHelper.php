<?php
declare(strict_types=1);

namespace Awesome\Frontend\Helper;

class TranslationFileHelper
{
    private const TRANSLATION_FILE_PATTERN = '/\/?i18n\/([a-z]{2}_[A-Z]{2})\.json$/';

    /**
     * Check if file matches the translation file pattern.
     * @param string $path
     * @return bool
     */
    public static function isTranslationFile(string $path): bool
    {
        return (bool) preg_match(self::TRANSLATION_FILE_PATTERN, $path);
    }

    /**
     * Get locale by translation file path, if any.
     * @param string $path
     * @return string|null
     */
    public static function getLocaleByPath(string $path): ?string
    {
        return preg_match(self::TRANSLATION_FILE_PATTERN, $path, $matches) ? $matches[1] : null;
    }
}