<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http;
use Awesome\Frontend\Helper\StaticContentHelper;
use Awesome\Frontend\Model\Css\CssMinifier;
use Awesome\Frontend\Model\Css\LessParser;

class Styles
{
    private const MODULE_LESS_PATTERN = '/*/*/view/{%s,%s}/web/css/source/module.less';
    public const RESULT_FILENAME = 'styles.css';

    private const PUBLIC_DIRECTORY_VARIABLE = 'pubDir';

    /**
     * @var CssMinifier $cssMinifier
     */
    private $cssMinifier;

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * @var FrontendState $frontendState
     */
    private $frontendState;

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
        $this->cssMinifier = $cssMinifier;
        $this->fileManager = $fileManager;
        $this->frontendState = $frontendState;
        $this->lessParser = $lessParser;
    }

    /**
     * Generate styles css file.
     * If developer mode is active, source map will be attached.
     * @param string $view
     * @return void
     */
    public function generate(string $view): void
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
            BP . StaticContent::STATIC_FOLDER_PATH . $view . '/' . $resultFile,
            $content,
            true
        );
    }
}
