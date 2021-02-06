<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Serializer\Json;
use Awesome\Frontend\Helper\StaticContentHelper;
use Awesome\Frontend\Model\DeployedVersion;

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
     * @var Json $json
     */
    private $json;

    /**
     * RequireJs constructor.
     * @param DeployedVersion $deployedVersion
     * @param FileManager $fileManager
     * @param FrontendState $frontendState
     * @param Json $json
     */
    public function __construct(
        DeployedVersion $deployedVersion,
        FileManager $fileManager,
        FrontendState $frontendState,
        Json $json
    ) {
        $this->deployedVersion = $deployedVersion;
        $this->fileManager = $fileManager;
        $this->frontendState = $frontendState;
        $this->json = $json;
    }

    /**
     * Generate requirejs config file.
     * @param string $view
     * @return void
     */
    public function generate(string $view): void
    {
        $requirePaths = [];

        foreach (glob(APP_DIR . sprintf(self::REQUIREJS_CONFIG_PATTERN, Http::BASE_VIEW, $view), GLOB_BRACE) as $configFile) {
            $config = $this->json->decode($this->fileManager->readFile($configFile));

            if (isset($config['paths'])) {
                $paths = $config['paths'];

                if ($this->frontendState->isJsMinificationEnabled()) {
                    foreach ($paths as $module => $path) {
                        $paths[$module] = StaticContentHelper::addMinificationFlag($path);
                    }
                }
                $requirePaths = array_replace_recursive($requirePaths, $paths);
            }
        }
        $deployedVersion = $this->deployedVersion->getVersion();

        $config = [
            'baseUrl' => ($this->frontendState->isPubRoot() ? '' : '/pub')
                . '/static/' . ($deployedVersion ? 'version' . $deployedVersion . '/' : '/') . $view,
            'paths'   => $requirePaths,
        ];

        if ($this->frontendState->isJsMinificationEnabled()) {
            $config = $this->json->encode($config);
        } else {
            $config = $this->json->prettyEncode($config);
        }

        $this->fileManager->createFile(
            BP . StaticContent::STATIC_FOLDER_PATH . $view . '/' . self::RESULT_FILENAME,
            'requirejs.config(' . $config . ');' . "\n",
            true
        );
    }
}
