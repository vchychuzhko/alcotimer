<?php
declare(strict_types=1);

namespace Vch\Frontend\Model\Generator;

use Vch\Framework\Model\FileManager;
use Vch\Frontend\Helper\StaticContentHelper;
use Vch\Frontend\Model\Css\CssMinifier;
use Vch\Frontend\Model\FrontendState;
use Vch\Frontend\Model\GeneratorInterface;
use Vch\Frontend\Model\Js\JsMinifier;

class StaticFile extends \Vch\Frontend\Model\AbstractGenerator
{
    public const CSS_FOLDER = 'css';
    public const FONTS_FOLDER = 'fonts';
    public const IMAGES_FOLDER = 'images';
    public const JS_FOLDER = 'js';

    private const LIB_FILE_PATTERN = '/(\/)?lib\/.*\.js$/';

    /**
     * @inheritDoc
     */
    protected static array $extensions = [
        'css',
        'js',
        'eot',
        'ttf',
        'otf',
        'woff',
        'woff2',
        'svg',
    ];

    private CssMinifier $cssMinifier;

    private JsMinifier $jsMinifier;

    /**
     * StaticFile constructor.
     * @param CssMinifier $cssMinifier
     * @param FileManager $fileManager
     * @param FrontendState $frontendState
     * @param JsMinifier $jsMinifier
     */
    public function __construct(
        CssMinifier $cssMinifier,
        FileManager $fileManager,
        FrontendState $frontendState,
        JsMinifier $jsMinifier
    ) {
        parent::__construct($fileManager, $frontendState);
        $this->cssMinifier = $cssMinifier;
        $this->jsMinifier = $jsMinifier;
    }

    /**
     * Deploy static file for specified view.
     * @inheritDoc
     */
    public function generate(string $path, string $view): string
    {
        switch (pathinfo($path, PATHINFO_EXTENSION)) {
            case 'eot':
            case 'ttf':
            case 'otf':
            case 'woff':
            case 'woff2': {
                return $this->generateFontFile($path, $view);
            }
            case 'svg': {
                return $this->generateImageFile($path, $view);
            }
            case 'css': {
                return $this->generateCssFile($path, $view);
            }
            case 'js': {
                return preg_match(self::LIB_FILE_PATTERN, $path)
                    ? $this->generateLibFile($path, $view)
                    : $this->generateJsFile($path, $view);
            }
        }

        return '';
    }

    /**
     * Deploy font file for specified view.
     * @param string $path
     * @param string $view
     * @return string
     */
    public function generateFontFile(string $path, string $view): string
    {
        $staticPath = $this->getStaticPath($path, $view);

        $this->fileManager->copyFile($path, $staticPath);

        return $this->fileManager->readFile($staticPath);
    }

    /**
     * Deploy image file for specified view.
     * @param string $path
     * @param string $view
     * @return string
     */
    public function generateImageFile(string $path, string $view): string
    {
        $staticPath = $this->getStaticPath($path, $view);

        $this->fileManager->copyFile($path, $staticPath);

        return $this->fileManager->readFile($staticPath);
    }

    /**
     * Deploy css static file for specified view.
     * @param string $path
     * @param string $view
     * @return string
     */
    public function generateCssFile(string $path, string $view): string
    {
        $staticPath = $this->getStaticPath($path, $view);

        if ($this->frontendState->isCssMinificationEnabled()) {
            if (StaticContentHelper::minifiedVersionExists($path)) {
                $path = StaticContentHelper::addMinificationFlag($path);
            }

            $staticPath = StaticContentHelper::addMinificationFlag($staticPath);
        }

        if ($this->frontendState->useSymlinkForCss()) {
            $this->fileManager->createSymlink($path, $staticPath);
        } else {
            $content = $this->fileManager->readFile($path);

            if ($this->frontendState->isCssMinificationEnabled() && !StaticContentHelper::isFileMinified($path)) {
                $content = $this->cssMinifier->minify($content);
            }

            $this->fileManager->createFile($staticPath, $content);
        }

        return $this->fileManager->readFile($staticPath);
    }

    /**
     * Deploy js static file for specified view.
     * @param string $path
     * @param string $view
     * @return string
     */
    public function generateJsFile(string $path, string $view): string
    {
        $staticPath = $this->getStaticPath($path, $view);

        if ($this->frontendState->isJsMinificationEnabled()) {
            if (StaticContentHelper::minifiedVersionExists($path)) {
                $path = StaticContentHelper::addMinificationFlag($path);
            }

            $staticPath = StaticContentHelper::addMinificationFlag($staticPath);
        }

        if ($this->frontendState->useSymlinkForJs()) {
            $this->fileManager->createSymlink($path, $staticPath);
        } else {
            $content = $this->fileManager->readFile($path);

            if ($this->frontendState->isJsMinificationEnabled() && !StaticContentHelper::isFileMinified($path)) {
                $content = $this->jsMinifier->minify($content);
            }

            $this->fileManager->createFile($staticPath, $content);
        }

        return $this->fileManager->readFile($staticPath);
    }

    /**
     * Deploy js library static file for specified view.
     * @param string $path
     * @param string $view
     * @return string
     */
    public function generateLibFile(string $path, string $view): string
    {
        $staticPath = $this->getStaticPath(str_replace(BP, '', $path), $view, true);

        if ($this->frontendState->isJsMinificationEnabled()) {
            if (StaticContentHelper::minifiedVersionExists($path)) {
                $path = StaticContentHelper::addMinificationFlag($path);
            }

            $staticPath = StaticContentHelper::addMinificationFlag($staticPath);
        }

        if ($this->frontendState->useSymlinkForJs()) {
            $this->fileManager->createSymlink($path, $staticPath);
        } else {
            $this->fileManager->copyFile($path, $staticPath);
        }

        return $this->fileManager->readFile($staticPath);
    }
}
