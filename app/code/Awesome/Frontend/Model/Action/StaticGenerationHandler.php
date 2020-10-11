<?php

namespace Awesome\Frontend\Model\Action;

use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Http\Response;
use Awesome\Framework\Model\Logger;
use Awesome\Frontend\Model\StaticContent;

/**
 * Class StaticGenerationHandler
 * @method getRequestedFile()
 */
class StaticGenerationHandler extends \Awesome\Framework\Model\AbstractAction
{
    private const STATIC_FILE_PATTERN = '/^(\/pub)?(\/static\/)(version.+?\/)?(%s|%s)\/(.*)$/';

    /**
     * Mime types for static files.
     */
    private const MIME_TYPES = [
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
    ];

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * @var Logger $logger
     */
    private $logger;

    /**
     * @var StaticContent $staticContent
     */
    private $staticContent;

    /**
     * StaticGenerationHandler constructor.
     * @param FileManager $fileManager
     * @param Logger $logger
     * @param StaticContent $staticContent
     * @param array $data
     */
    public function __construct(
        FileManager $fileManager,
        Logger $logger,
        StaticContent $staticContent,
        $data = []
    ) {
        parent::__construct($data);
        $this->fileManager = $fileManager;
        $this->logger = $logger;
        $this->staticContent = $staticContent;
    }

    /**
     * Generate static files and return content for requested one.
     * @inheritDoc
     * @throws \Exception
     */
    public function execute($request)
    {
        $path = $this->getRequestedFile();
        $view = preg_replace(sprintf(self::STATIC_FILE_PATTERN, Http::FRONTEND_VIEW, Http::BACKEND_VIEW), '$4', $request->getPath());

        $this->staticContent->deploy($view);

        $staticPath = preg_replace(sprintf(self::STATIC_FILE_PATTERN, Http::FRONTEND_VIEW, Http::BACKEND_VIEW), '$4/$5', $request->getPath());

        $content = $this->fileManager->readFile(BP . StaticContent::STATIC_FOLDER_PATH . $staticPath);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $headers = [];

        if (isset(self::MIME_TYPES[$extension])) {
            $headers = ['Content-Type' => self::MIME_TYPES[$extension]];
        }
        $this->logger->info(sprintf('Static file "%s" was requested and generated', $request->getPath()));

        return new Response($content, Response::SUCCESS_STATUS_CODE, $headers);
    }
}
