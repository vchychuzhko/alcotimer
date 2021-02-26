<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Locale;
use Awesome\Framework\Model\Locale\Translator;
use Awesome\Framework\Model\Serializer\Json;

class Translation
{
    private const JS_FOLDERS_PATTERN = '/*/*/view/{%s,%s}/web/js';

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * @var FrontendState $frontendState
     */
    private $frontendState;

    /**
     * @var Json $json
     */
    private $json;

    /**
     * @var Locale $locale
     */
    private $locale;

    /**
     * @var Translator $translator
     */
    private $translator;

    /**
     * Translation constructor.
     * @param FileManager $fileManager
     * @param FrontendState $frontendState
     * @param Json $json
     * @param Locale $locale
     * @param Translator $translator
     */
    public function __construct(
        FileManager $fileManager,
        FrontendState $frontendState,
        Json $json,
        Locale $locale,
        Translator $translator
    ) {
        $this->fileManager = $fileManager;
        $this->frontendState = $frontendState;
        $this->json = $json;
        $this->locale = $locale;
        $this->translator = $translator;
    }

    /**
     * Generate translations file for all locales.
     * @param string $view
     * @return void
     */
    public function generate(string $view): void
    {
        foreach ($this->locale->getAllLocales() as $locale) {
            $this->generateLocale($view, $locale);
        }
    }

    /**
     * Generate translations file for specified locale.
     * Current or default locale will be used if not provided.
     * @param string $view
     * @param string|null $locale
     * @return void
     */
    public function generateLocale(string $view, ?string $locale = null): void
    {
        $locale = $locale ?: $this->locale->getLocale();
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

        if ($this->frontendState->isJsMinificationEnabled()) {
            $content = $this->json->encode($dictionary);
        } else {
            $content = $this->json->prettyEncode($dictionary);
        }

        $this->fileManager->createFile(
            BP . StaticContent::STATIC_FOLDER_PATH . $view . '/i18n/' . $locale . '.json',
            $content,
            true
        );
    }
}
