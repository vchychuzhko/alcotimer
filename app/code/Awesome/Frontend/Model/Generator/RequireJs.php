<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\Generator;

use Awesome\Framework\Model\FileManager;
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

    public const MODULE_LOAD_TIMEOUT = 15;

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
        parent::__construct($fileManager, $frontendState);
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
        $requirePaths = [];

        foreach (glob(APP_DIR . sprintf(self::REQUIREJS_CONFIG_PATTERN, Http::BASE_VIEW, $view), GLOB_BRACE) as $configFile) {
            $config = $this->json->decode($this->fileManager->readFile($configFile));

            if (isset($config['paths'])) {
                $requirePaths = array_replace($requirePaths, $config['paths']);
            }
        }
        $deployedVersion = $this->deployedVersion->getVersion();

        $config = $this->json->prettyEncode([
            'baseUrl'     => '/static/' . ($deployedVersion ? 'version' . $deployedVersion . '/' : '/') . $view,
            'paths'       => $requirePaths,
            'waitSeconds' => self::MODULE_LOAD_TIMEOUT,
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
let context = require.s.contexts._,
    originalNameToUrl = context.nameToUrl;

context.nameToUrl = (...args) => originalNameToUrl.apply(context, args).replace(/(\.min)?\.js$/, '.min.js');
JS;
    }
}
