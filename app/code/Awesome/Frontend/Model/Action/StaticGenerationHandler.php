<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\Action;

use Awesome\Framework\Model\AppState;
use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Http\Response;
use Awesome\Framework\Model\Http\Request;
use Awesome\Frontend\Model\StaticContent;

/**
 * Class StaticGenerationHandler
 * @method string getRequestedFile()
 */
class StaticGenerationHandler extends \Awesome\Framework\Model\AbstractAction
{
    private const STATIC_FILE_PATTERN = '/^(\/pub)?(\/static\/)(version.+?\/)?(%s|%s)\/(.*)$/';

    /**
     * Mime types for static files.
     */
    private const MIME_TYPES = [
        'css' => 'text/css',
        'html' => 'text/html',
        'js' => 'application/javascript',
        'json' => 'application/json',
    ];

    /**
     * @var AppState $appState
     */
    private $appState;

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * @var StaticContent $staticContent
     */
    private $staticContent;

    /**
     * StaticGenerationHandler constructor.
     * @param AppState $appState
     * @param FileManager $fileManager
     * @param StaticContent $staticContent
     * @param array $data
     */
    public function __construct(
        AppState $appState,
        FileManager $fileManager,
        StaticContent $staticContent,
        array $data = []
    ) {
        parent::__construct($data);
        $this->appState = $appState;
        $this->fileManager = $fileManager;
        $this->staticContent = $staticContent;
    }

    /**
     * Generate static files and return content for requested one.
     * In case developer mode is on, only requested file gets generated.
     * @inheritDoc
     * @throws \Exception
     */
    public function execute(Request $request): Response
    {
        $path = $this->getRequestedFile();
        $view = preg_replace(
            sprintf(self::STATIC_FILE_PATTERN, Http::FRONTEND_VIEW, Http::BACKEND_VIEW), '$4', $request->getPath()
        );

        if ($this->appState->isDeveloperMode()) {
            $this->staticContent->deployFile($path, $view);
        } else {
            $this->staticContent->deploy($view);
        }

        $staticPath = preg_replace(
            sprintf(self::STATIC_FILE_PATTERN, Http::FRONTEND_VIEW, Http::BACKEND_VIEW), '$5', $request->getPath()
        );

        $content = $this->fileManager->readFile(BP . StaticContent::STATIC_FOLDER_PATH . $view . '/' . $staticPath);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $headers = [];

        if (isset(self::MIME_TYPES[$extension])) {
            $headers = ['Content-Type' => self::MIME_TYPES[$extension]];
        }

        return new Response($content, Response::SUCCESS_STATUS_CODE, $headers);
    }
}
