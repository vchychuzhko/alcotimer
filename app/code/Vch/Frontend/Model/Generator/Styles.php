<?php
declare(strict_types=1);

namespace Vch\Frontend\Model\Generator;

use Vch\Framework\Model\FileManager;
use Vch\Framework\Model\Http;
use Vch\Frontend\Helper\StaticContentHelper;
use Vch\Frontend\Model\Css\CssMinifier;
use Vch\Frontend\Model\Css\LessParser;
use Vch\Frontend\Model\FrontendState;

class Styles extends \Vch\Frontend\Model\AbstractGenerator
{
    private const MODULE_LESS_PATTERN = '/*/*/view/{%s,%s}/web/css/source/module.less';
    public const RESULT_FILENAME = 'styles.css';

    private CssMinifier $cssMinifier;

    private LessParser $lessParser;

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
    public function generate(string $path, string $view): string
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

        return $content;
    }

    /**
     * @inheritDoc
     */
    public static function match(string $path): bool
    {
        return $path === self::RESULT_FILENAME;
    }
}
