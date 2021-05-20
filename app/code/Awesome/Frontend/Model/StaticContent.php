<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http;
use Awesome\Frontend\Helper\StaticContentHelper;
use Awesome\Frontend\Model\Generator\RequireJs;
use Awesome\Frontend\Model\Generator\StaticFile;
use Awesome\Frontend\Model\Generator\Styles;
use Awesome\Frontend\Model\Generator\Translation;

class StaticContent implements \Awesome\Framework\Model\SingletonInterface
{
    public const STATIC_FOLDER_PATH = '/pub/static/';

    private const STATIC_PATH_PATTERN = '/*/*/view/%s/web/%s';
    private const LIB_PATH_PATTERN = '/lib/*/*.js';

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * @var RequireJs $requireJs
     */
    private $requireJs;

    /**
     * @var StaticFile $staticFile
     */
    private $staticFile;

    /**
     * @var Styles $styles
     */
    private $styles;

    /**
     * @var Translation $translation
     */
    private $translation;

    /**
     * StaticContent constructor.
     * @param FileManager $fileManager
     * @param RequireJs $requireJs
     * @param StaticFile $staticFile
     * @param Styles $styles
     * @param Translation $translation
     */
    public function __construct(
        FileManager $fileManager,
        RequireJs $requireJs,
        StaticFile $staticFile,
        Styles $styles,
        Translation $translation
    ) {
        $this->fileManager = $fileManager;
        $this->requireJs = $requireJs;
        $this->staticFile = $staticFile;
        $this->styles = $styles;
        $this->translation = $translation;
    }

    /**
     * Deploy static files for a specified view.
     * @param string $view
     * @return $this
     */
    public function deploy(string $view): self
    {
        $this->fileManager->removeDirectory(BP . self::STATIC_FOLDER_PATH . $view);

        $this->generateFiles($view);

        $this->requireJs->generate(RequireJs::RESULT_FILENAME, $view);
        $this->styles->generate(Styles::RESULT_FILENAME, $view);
        $this->translation->generateAll($view);

        return $this;
    }

    /**
     * Collect, parse and generate static files for specified view.
     * @param string $view
     * @return $this
     */
    private function generateFiles(string $view): self
    {
        $fontPattern = APP_DIR . sprintf(self::STATIC_PATH_PATTERN, '{' . Http::BASE_VIEW . ',' . $view . '}', 'fonts');

        foreach (glob($fontPattern, GLOB_ONLYDIR | GLOB_BRACE) as $folder) {
            $files = $this->fileManager->scanDirectory($folder, true, ['eot', 'ttf', 'otf', 'woff', 'woff2']);

            foreach ($files as $file) {
                $this->staticFile->generateFontFile($file, $view);
            }
        }

        $cssPattern = APP_DIR . sprintf(self::STATIC_PATH_PATTERN, '{' . Http::BASE_VIEW . ',' . $view . '}', 'css');

        foreach ($this->globWithoutMinifiedFiles($cssPattern, GLOB_ONLYDIR | GLOB_BRACE) as $folder) {
            $files = $this->fileManager->scanDirectory($folder, true, 'css');

            foreach ($files as $file) {
                $this->staticFile->generateCssFile($file, $view);
            }
        }

        $jsPattern = APP_DIR . sprintf(self::STATIC_PATH_PATTERN, '{' . Http::BASE_VIEW . ',' . $view . '}', 'js');

        foreach ($this->globWithoutMinifiedFiles($jsPattern, GLOB_ONLYDIR | GLOB_BRACE) as $folder) {
            $files = $this->fileManager->scanDirectory($folder, true, 'js');

            foreach ($files as $file) {
                $this->staticFile->generateJsFile($file, $view);
            }
        }

        $libFiles = $this->globWithoutMinifiedFiles(BP . self::LIB_PATH_PATTERN);

        foreach ($libFiles as $libFile) {
            $this->staticFile->generateLibFile($libFile, $view);
        }

        return $this;
    }

    /**
     * Perform glob, skipping minified files.
     * @param string $pattern
     * @param int $flags
     * @return array
     */
    private function globWithoutMinifiedFiles(string $pattern, int $flags = 0): array
    {
        $files = glob($pattern, $flags) ?: [];

        return array_filter($files, static function ($file) {
            return !StaticContentHelper::isFileMinified($file);
        });
    }
}
