<?php
declare(strict_types=1);

namespace Vch\Frontend\Model\Generator;

use Vch\Framework\Model\Config;
use Vch\Framework\Model\FileManager\JsonFileManager;
use Vch\Framework\Model\Http;
use Vch\Framework\Model\Serializer\Json;
use Vch\Frontend\Helper\StaticContentHelper;
use Vch\Frontend\Model\DeployedVersion;
use Vch\Frontend\Model\FrontendState;
use Vch\Frontend\Model\Js\JsMinifier;

class RequireJs extends \Vch\Frontend\Model\AbstractGenerator
{
    private const REQUIREJS_CONFIG_PATTERN = '/*/*/view/{%s,%s}/requirejs-config.json';
    public const RESULT_FILENAME = 'requirejs-config.js';

    private Config $config;

    private DeployedVersion $deployedVersion;

    private JsMinifier $jsMinifier;

    private Json $json;

    /**
     * RequireJs constructor.
     * @param Config $config
     * @param DeployedVersion $deployedVersion
     * @param JsonFileManager $jsonFileManager
     * @param FrontendState $frontendState
     * @param JsMinifier $jsMinifier
     * @param Json $json
     */
    public function __construct(
        Config $config,
        DeployedVersion $deployedVersion,
        JsonFileManager $jsonFileManager,
        FrontendState $frontendState,
        JsMinifier $jsMinifier,
        Json $json
    ) {
        parent::__construct($jsonFileManager, $frontendState);
        $this->config = $config;
        $this->deployedVersion = $deployedVersion;
        $this->jsMinifier = $jsMinifier;
        $this->json = $json;
    }

    /**
     * Generate requirejs config file.
     * @inheritDoc
     */
    public function generate(string $path, string $view): string
    {
        $resultFile = self::RESULT_FILENAME;
        $paths = [];
        $mixins = [];

        foreach (glob(APP_DIR . sprintf(self::REQUIREJS_CONFIG_PATTERN, Http::BASE_VIEW, $view), GLOB_BRACE) as $configFile) {
            $requireConfig = $this->fileManager->parseJsonFile($configFile);

            if (isset($requireConfig['paths'])) {
                $paths = array_replace($paths, $requireConfig['paths']);
            }

            if (isset($requireConfig['mixins'])) {
                $mixins = array_merge_recursive($mixins, $requireConfig['mixins']);
            }
        }

        foreach ($mixins as $original => $mixin) {
            foreach ($mixin as $rewrite => $condition) {
                if ($condition === true || $this->config->get($condition)) {
                    $paths[$original] = $rewrite;
                }
            }
        }

        $deployedVersion = $this->deployedVersion->getVersion();

        $resultConfig = $this->json->prettyEncode([
            'baseUrl'     => '/static/' . ($deployedVersion ? 'version' . $deployedVersion . '/' : '/') . $view,
            'paths'       => $paths
        ]);
        $content = <<<JS
requirejs.config($resultConfig);

JS;

        if ($this->frontendState->isJsMinificationEnabled()) {
            $content .= $this->getMinResolver();

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
     * @inheritDoc
     */
    public static function match(string $path): bool
    {
        return $path === self::RESULT_FILENAME;
    }

    /**
     * Get min file path resolver for requirejs configuration.
     * @return string
     */
    private function getMinResolver(): string
    {
        return <<<JS
const context = require.s.contexts._;
const originalNameToUrl = context.nameToUrl;

context.nameToUrl = (...args) => originalNameToUrl.apply(context, args).replace(/(\.min)?\.js$/, '.min.js');
JS;
    }
}
