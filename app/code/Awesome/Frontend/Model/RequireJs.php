<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Framework\Model\AppState;
use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Serializer\Json;

class RequireJs
{
    private const REQUIREJS_CONFIG_PATTERN = '/*/*/view/{%s,%s}/requirejs-config.json';
    public const RESULT_FILENAME = 'requirejs-config.js';

    /**
     * @var AppState $appState
     */
    private $appState;

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
     * @param AppState $appState
     * @param FileManager $fileManager
     * @param Json $json
     */
    public function __construct(AppState $appState, FileManager $fileManager, Json $json)
    {
        $this->appState = $appState;
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
                $requirePaths = array_replace_recursive($requirePaths, $config['paths']);
            }
        }

        $config = $this->json->prettyEncode([
            'baseUrl' => ($this->appState->isPubRoot() ? '' : 'pub/')
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
