<?php
declare(strict_types=1);

namespace Vch\Frontend\Model\Generator;

use Vch\Framework\Model\FileManager;
use Vch\Framework\Model\Http;
use Vch\Framework\Model\Locale;
use Vch\Framework\Model\Locale\Translator;
use Vch\Framework\Model\Serializer\Json;
use Vch\Frontend\Helper\StaticContentHelper;
use Vch\Frontend\Model\FrontendState;
use Vch\Frontend\Model\Js\JsMinifier;

class Translation extends \Vch\Frontend\Model\AbstractGenerator
{
    private const JS_FOLDERS_PATTERN = '/*/*/view/{%s,%s}/web/js';
    private const TRANSLATION_FILE_PATTERN = '/\/?i18n\/([a-z]{2}_[A-Z]{2})(\.min)?\.js$/';

    private JsMinifier $jsMinifier;

    private Json $json;

    private Translator $translator;

    /**
     * Translation constructor.
     * @param FileManager $fileManager
     * @param FrontendState $frontendState
     * @param JsMinifier $jsMinifier
     * @param Json $json
     * @param Translator $translator
     */
    public function __construct(
        FileManager $fileManager,
        FrontendState $frontendState,
        JsMinifier $jsMinifier,
        Json $json,
        Translator $translator
    ) {
        parent::__construct($fileManager, $frontendState);
        $this->jsMinifier = $jsMinifier;
        $this->json = $json;
        $this->translator = $translator;
    }

    /**
     * Generate translations file for specified view and locale.
     * @inheritDoc
     */
    public function generate(string $path, string $view): string
    {
        $locale = self::getLocaleByPath($path);
        $resultFile = self::getPathByLocale($locale);
        $dictionary = [];
        $phrases = [];

        $jsPattern = APP_DIR . sprintf(self::JS_FOLDERS_PATTERN, Http::BASE_VIEW, $view);

        foreach (glob($jsPattern, GLOB_ONLYDIR | GLOB_BRACE) as $folder) {
            $files = $this->fileManager->scanDirectory($folder, true, 'js');

            foreach ($files as $file) {
                $content = preg_replace('~(["\'`])\s*?\+\s*?\1~', '', $this->fileManager->readFile($file)); // concatenation

                $result = preg_match_all('~__\((["\'`])(.*?[^\\\\])\1(.*?)\)~', $content, $matches);

                if ($result && isset($matches[2])) {
                    foreach ($matches[2] as $phrase) {
                        $phrases[] = str_replace(["\'", '\"', '\`'], ["'", '"', '`'], $phrase);
                    }
                }
            }
        }

        foreach ($phrases as $phrase) {
            $translate = $this->translator->translate($phrase, $locale);

            if ($translate !== $phrase) {
                $dictionary[$phrase] = $translate;
            }
        }
        ksort($dictionary);

        $dictionary = $this->json->prettyEncode($dictionary);

        $content = <<<JS
define($dictionary);

JS;

        if ($this->frontendState->isJsMinificationEnabled()) {
            $content = $this->jsMinifier->minify($content);
            $resultFile = StaticContentHelper::addMinificationFlag($resultFile);
        }

        $this->fileManager->createFile(
            $this->getStaticPath($resultFile, $view, true),
            $content,
            true
        );

        return $content;
    }

    /**
     * Generate translations file for all locales.
     * @param string $view
     */
    public function generateAll(string $view)
    {
        foreach (Locale::getAllLocales() as $locale) {
            $this->generate(self::getPathByLocale($locale), $view);
        }
    }

    /**
     * @inheritDoc
     */
    public static function match(string $path): bool
    {
        return in_array(self::getLocaleByPath($path), Locale::getAllLocales(), true);
    }

    /**
     * Get locale by translation file path, if any.
     * @param string $path
     * @return string|null
     */
    private static function getLocaleByPath(string $path): ?string
    {
        return preg_match(self::TRANSLATION_FILE_PATTERN, $path, $matches) ? $matches[1] : null;
    }

    /**
     * Get translation file path by by locale.
     * @param string $locale
     * @return string
     */
    private static function getPathByLocale(string $locale): string
    {
        return '/i18n/' . $locale . '.js';
    }
}
