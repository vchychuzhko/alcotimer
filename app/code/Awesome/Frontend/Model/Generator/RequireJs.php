<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\Generator;

use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\FileManager\JsonFileManager;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Serializer\Json;
use Awesome\Frontend\Helper\StaticContentHelper;
use Awesome\Frontend\Model\DeployedVersion;
use Awesome\Frontend\Model\FrontendState;
use Awesome\Frontend\Model\GeneratorInterface;
use Awesome\Frontend\Model\Js\JsMinifier;

class RequireJs extends \Awesome\Frontend\Model\AbstractGenerator
{
    private const REQUIREJS_CONFIG_PATTERN = '/*/*/view/{%s,%s}/requirejs-config.json';
    public const RESULT_FILENAME = 'requirejs-config.js';

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var DeployedVersion $deployedVersion
     */
    private $deployedVersion;

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
    public function generate(string $path, string $view): GeneratorInterface
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

        return $this;
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
