<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Locale;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\FileManager\CsvFileManager;
use Awesome\Framework\Model\Locale;
use Awesome\Framework\Model\Logger;

class Translator
{
    private const TRANSLATION_FILES_PATTERN = '/*/*/i18n/%s.csv';

    private Cache $cache;

    private CsvFileManager $csvFileManager;

    private Locale $locale;

    private Logger $logger;

    private array $dictionary;

    /**
     * Translator constructor.
     * @param Cache $cache
     * @param CsvFileManager $csvFileManager
     * @param Locale $locale
     * @param Logger $logger
     */
    public function __construct(
        Cache $cache,
        CsvFileManager $csvFileManager,
        Locale $locale,
        Logger $logger
    ) {
        $this->cache = $cache;
        $this->csvFileManager = $csvFileManager;
        $this->locale = $locale;
        $this->logger = $logger;
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

        if (!isset($this->dictionary[$locale])) {
            $this->dictionary[$locale] = $this->cache->get(Cache::TRANSLATIONS_CACHE_KEY, $locale, function () use ($locale) {
                $translations = [];

                foreach (glob(APP_DIR . sprintf(self::TRANSLATION_FILES_PATTERN, $locale)) as $translationFile) {
                    $translationData = [];

                    foreach ($this->csvFileManager->parseFile($translationFile) as $line => $translation) {
                        if (!isset($translation[0], $translation[1])) {
                            $this->logger->info(sprintf('Translation CSV file "%s" is not valid on line %s', $translationFile, $line + 1), Logger::INFO_CRITICAL_LEVEL);

                            continue;
                        }

                        $translationData[$translation[0]] = $translation[1];
                    }

                    $translations = array_merge($translations, $translationData);
                }

                return $translations;
            });
        }

        return $this->dictionary[$locale];
    }
}
