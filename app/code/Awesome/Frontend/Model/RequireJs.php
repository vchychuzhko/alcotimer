<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Serializer\Json;
use Awesome\Frontend\Helper\StaticContentHelper;

class RequireJs
{
    private const REQUIREJS_CONFIG_PATTERN = '/*/*/view/{%s,%s}/requirejs-config.json';
    public const RESULT_FILENAME = 'requirejs-config.js';

    /**
     * @var FrontendState $frontendState
     */
    private $frontendState;

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * @var Json $json
     */
    private $json;

    /**
     * RequireJs constructor.
     * @param FrontendState $frontendState
     * @param FileManager $fileManager
     * @param Json $json
     */
    public function __construct(FrontendState $frontendState, FileManager $fileManager, Json $json)
    {
        $this->frontendState = $frontendState;
        $this->fileManager = $fileManager;
        $this->json = $json;
    }

    /**
     * Generate requirejs config file.
     * @param string $view
     * @param int|null $deployedVersion
     * @return void
     */
    public function generate(string $view, ?int $deployedVersion = null): void
    {
        $requirePaths = [];

        foreach (glob(APP_DIR . sprintf(self::REQUIREJS_CONFIG_PATTERN, Http::BASE_VIEW, $view), GLOB_BRACE) as $configFile) {
            $config = $this->json->decode($this->fileManager->readFile($configFile));

            if (isset($config['paths'])) {
                $paths = $config['paths'];

                if ($this->frontendState->isJsMinificationEnabled()) {
                    foreach ($paths as &$path) {
                        $path .= StaticContentHelper::MINIFICATION_FLAG;
                    }
                }
                $requirePaths = array_replace_recursive($requirePaths, $paths);
            }
        }

        $config = $this->json->prettyEncode([
            'baseUrl' => ($this->frontendState->isPubRoot() ? '' : 'pub/')
                . 'static/' . $view . ($deployedVersion ? '/version' . $deployedVersion : ''),
            'paths' => $requirePaths,
        ]);

        $this->fileManager->createFile(
            BP . StaticContent::STATIC_FOLDER_PATH . $view . '/' . self::RESULT_FILENAME,
            'requirejs.config(' . $config . ');' . "\n",
            true
        );
    }
}
