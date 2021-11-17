<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\Generator;

use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http;
use Awesome\Frontend\Helper\StaticContentHelper;
use Awesome\Frontend\Model\Css\CssMinifier;
use Awesome\Frontend\Model\Css\LessParser;
use Awesome\Frontend\Model\FrontendState;
use Awesome\Frontend\Model\GeneratorInterface;

class Styles extends \Awesome\Frontend\Model\AbstractGenerator
{
    private const MODULE_LESS_PATTERN = '/*/*/view/{%s,%s}/web/css/source/module.less';
    public const RESULT_FILENAME = 'styles.css';

    /**
     * @var CssMinifier $cssMinifier
     */
    private $cssMinifier;

    /**
     * @var LessParser $lessParser
     */
    private $lessParser;

    /**
     * Styles constructor.
     * @param CssMinifier $cssMinifier
     * @param FileManager $fileManager
     * @param FrontendState $frontendState
     * @param LessParser $lessParser
     */
    public function __construct(
        CssMinifier $cssMinifier,
        FileManager $fileManager,
        FrontendState $frontendState,
        LessParser $lessParser
    ) {
        parent::__construct($fileManager, $frontendState);
        $this->cssMinifier = $cssMinifier;
        $this->lessParser = $lessParser;
    }

    /**
     * Generate styles css file for specified view..
     * If developer mode is active, source map will be attached.
     * @inheritDoc
     */
    public function generate(string $path, string $view): GeneratorInterface
    {
        $resultFile = self::RESULT_FILENAME;
        $this->lessParser->reset();

        if ($this->frontendState->isDeveloperMode()) {
            $this->lessParser->enableSourceMap();
        }

        foreach (glob(APP_DIR . sprintf(self::MODULE_LESS_PATTERN, Http::BASE_VIEW, $view), GLOB_BRACE) as $moduleFile) {
            $this->lessParser->addFile($moduleFile);
        }
        $content = $this->lessParser->getCss();

        if ($this->frontendState->isCssMinificationEnabled()) {
            if ($this->frontendState->isDeveloperMode()) {
                $this->cssMinifier->keepSourceMap();
            }

            $content = $this->cssMinifier->minify($content);
            $resultFile = StaticContentHelper::addMinificationFlag($resultFile);
        }

        $this->fileManager->createFile(
            $this->getStaticPath($resultFile, $view, true),
            $content,
            true
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function match(string $path): bool
    {
        return $path === self::RESULT_FILENAME;
    }
}
