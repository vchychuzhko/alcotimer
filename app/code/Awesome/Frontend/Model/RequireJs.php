<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Serializer\Json;
use Awesome\Frontend\Helper\StaticContentHelper;
use Awesome\Frontend\Model\DeployedVersion;
use Awesome\Frontend\Model\Js\JsMinifier;

class RequireJs
{
    private const REQUIREJS_CONFIG_PATTERN = '/*/*/view/{%s,%s}/requirejs-config.json';
    public const RESULT_FILENAME = 'requirejs-config.js';

    /**
     * @var DeployedVersion $deployedVersion
     */
    private $deployedVersion;

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * @var FrontendState $frontendState
     */
    private $frontendState;

    /**
     * @var JsMinifier $jsMinifier
     */
    private $jsMinifier;

    /**
     * @var Json $json
     */
    private $json;

    /**
     * RequireJs constructor.
     * @param DeployedVersion $deployedVersion
     * @param FileManager $fileManager
     * @param FrontendState $frontendState
     * @param JsMinifier $jsMinifier
     * @param Json $json
     */
    public function __construct(
        DeployedVersion $deployedVersion,
        FileManager $fileManager,
        FrontendState $frontendState,
        JsMinifier $jsMinifier,
        Json $json
    ) {
        $this->deployedVersion = $deployedVersion;
        $this->fileManager = $fileManager;
        $this->frontendState = $frontendState;
        $this->jsMinifier = $jsMinifier;
        $this->json = $json;
    }

    /**
     * Generate requirejs config file.
     * @param string $view
     * @return void
     */
    public function generate(string $view): void
    {
        $resultFile = self::RESULT_FILENAME;
        $requirePaths = [];

        foreach (glob(APP_DIR . sprintf(self::REQUIREJS_CONFIG_PATTERN, Http::BASE_VIEW, $view), GLOB_BRACE) as $configFile) {
            $config = $this->json->decode($this->fileManager->readFile($configFile));

            if (isset($config['paths'])) {
                $requirePaths = array_replace($requirePaths, $config['paths']);
            }
        }
        $deployedVersion = $this->deployedVersion->getVersion();

        $config = $this->json->prettyEncode([
            'baseUrl' => '/static/' . ($deployedVersion ? 'version' . $deployedVersion . '/' : '/') . $view,
            'paths'   => $requirePaths,
        ]);
        $content = <<<JS
requirejs.config($config);

JS;

        if ($this->frontendState->isJsMinificationEnabled()) {
            $content .= $this->getMinResolver();

            $content = $this->jsMinifier->minify($content);
            $resultFile = StaticContentHelper::addMinificationFlag($resultFile);
        }

        $this->fileManager->createFile(
            BP . StaticContent::STATIC_FOLDER_PATH . $view . '/' . $resultFile,
            $content,
            true
        );
    }

    /**
     * Get min file path resolver for requirejs configuration.
     * @return string
     */
    private function getMinResolver(): string
    {
        return <<<JS
let context = require.s.contexts._,
    originalNameToUrl = context.nameToUrl;

context.nameToUrl = function () {
    let url = originalNameToUrl.apply(context, arguments);

    return url.replace(/(\.min)?\.js$/, '.min.js');
};
JS;
    }
}
