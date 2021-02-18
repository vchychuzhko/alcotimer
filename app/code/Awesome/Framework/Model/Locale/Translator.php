<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Locale;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\FileManager\CsvFileManager;
use Awesome\Framework\Model\Locale;

class Translator implements \Awesome\Framework\Model\SingletonInterface
{
    private const TRANSLATION_FILES_PATTERN = '/*/*/i18n/%s.csv';

    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var CsvFileManager $csvFileManager
     */
    private $csvFileManager;

    /**
     * @var Locale $locale
     */
    private $locale;

    /**
     * @var array $translations
     */
    private $translations;

    /**
     * Translator constructor.
     * @param Cache $cache
     * @param CsvFileManager $csvFileManager
     * @param Locale $locale
     */
    public function __construct(Cache $cache, CsvFileManager $csvFileManager, Locale $locale)
    {
        $this->cache = $cache;
        $this->csvFileManager = $csvFileManager;
        $this->locale = $locale;
    }

    /**
     * Translate provided text according to specified locale.
     * Current one is used if no locale is provided.
     * @param string $text
     * @param string|null $locale
     * @return string
     */
    public function translate(string $text, ?string $locale = null): string
    {
        $translations = $this->getTranslations($locale);

        return $translations[$text] ?? $text;
    }

    /**
     * Collect and parse translations for specified locale.
     * Current one is used if no locale is provided.
     * @param string|null $locale
     * @return array
     */
    private function getTranslations(?string $locale = null): array
    {
        $locale = $locale ?: $this->locale->getLocale();

        if (!isset($this->translations[$locale])) {
            $this->translations[$locale] = $this->cache->get(Cache::TRANSLATIONS_CACHE_KEY, $locale, function () use ($locale) {
                $translations = [];

                foreach (glob(APP_DIR . sprintf(self::TRANSLATION_FILES_PATTERN, $locale)) as $translationFile) {
                    $translationData = [];

                    foreach ($this->csvFileManager->parseFile($translationFile) as $translation) {
                        if (!isset($translation[0], $translation[1])) {
                            throw new \RuntimeException(sprintf('Translation CSV file is not valid: %s', $translationFile));
                        }
                        $translationData[$translation[0]] = $translation[1];
                    }

                    $translations = array_merge($translations, $translationData);
                }

                return $translations;
            });
        }

        return $this->translations[$locale];
    }
}
