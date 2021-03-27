<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\Generator;

use Awesome\Framework\Model\FileManager;
use Awesome\Frontend\Helper\StaticContentHelper;
use Awesome\Frontend\Model\Css\CssMinifier;
use Awesome\Frontend\Model\FrontendState;
use Awesome\Frontend\Model\GeneratorInterface;
use Awesome\Frontend\Model\Js\JsMinifier;

class StaticFile extends \Awesome\Frontend\Model\AbstractGenerator
{
    private const LIB_FILE_PATTERN = '/(\/)?lib\/.*\.js$/';

    /**
     * @inheritDoc
     */
    public static $extensions = [
        'css',
        'js',
        'eot',
        'ttf',
        'otf',
        'woff',
        'woff2',
    ];

    /**
     * @var CssMinifier $cssMinifier
     */
    private $cssMinifier;

    /**
     * @var JsMinifier $jsMinifier
     */
    private $jsMinifier;

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
    public function generate(string $path, string $view): GeneratorInterface
    {
        switch (pathinfo($path, PATHINFO_EXTENSION)) {
            case 'eot':
            case 'ttf':
            case 'otf':
            case 'woff':
            case 'woff2': {
                $this->generateFontFile($path, $view);
                break;
            }
            case 'css': {
                $this->generateCssFile($path, $view);
                break;
            }
            case 'js': {
                if (preg_match(self::LIB_FILE_PATTERN, $path)) {
                    $this->generateLibFile($path, $view);
                } else {
                    $this->generateJsFile($path, $view);
                }
                break;
            }
        }

        return $this;
    }

    /**
     * Deploy font file for specified view.
     * @param string $path
     * @param string $view
     * @return $this
     */
    public function generateFontFile(string $path, string $view): self
    {
        $staticPath = $this->getStaticPath($path, $view);

        $this->fileManager->copyFile($path, $staticPath);

        return $this;
    }

    /**
     * Deploy css static file for specified view.
     * @param string $path
     * @param string $view
     * @return $this
     */
    public function generateCssFile(string $path, string $view): self
    {
        $staticPath = $this->getStaticPath($path, $view);

        if ($this->frontendState->isCssMinificationEnabled()) {
            if (StaticContentHelper::minifiedVersionExists($path)) {
                $path = StaticContentHelper::addMinificationFlag($path);
            }

            $staticPath = StaticContentHelper::addMinificationFlag($staticPath);
        }

        if ($this->frontendState->useSymlinkForJs()) {
            $this->fileManager->createSymlink($path, $staticPath);
        } else {
            $content = $this->fileManager->readFile($path);

            if ($this->frontendState->isCssMinificationEnabled() && !StaticContentHelper::isFileMinified($path)) {
                $content = $this->cssMinifier->minify($content);
            }

            $this->fileManager->createFile($staticPath, $content);
        }

        return $this;
    }

    /**
     * Deploy js static file for specified view.
     * @param string $path
     * @param string $view
     * @return $this
     */
    public function generateJsFile(string $path, string $view): self
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

        return $this;
    }

    /**
     * Deploy js library static file for specified view.
     * @param string $path
     * @param string $view
     * @return $this
     */
    public function generateLibFile(string $path, string $view): self
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

        return $this;
    }
}
