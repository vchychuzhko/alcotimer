<?php

namespace Awesome\Frontend\Model\Action;

use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http\Response;
use Awesome\Frontend\Model\StaticContent;

class StaticGenerationHandler implements \Awesome\Framework\Model\ActionInterface
{
    /**
     * Mime types for static files.
     */
    private const MIME_TYPES = [
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
    ];

    /**
     * @var StaticContent $staticContent
     */
    private $staticContent;

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * StaticGenerationHandler constructor.
     * @param StaticContent $staticContent
     * @param FileManager $fileManager
     */
    public function __construct(StaticContent $staticContent, FileManager $fileManager)
    {
        $this->staticContent = $staticContent;
        $this->fileManager = $fileManager;
    }

    /**
     * Generate static files and return content for requested one.
     * @inheritDoc
     * @throws \Exception
     */
    public function execute($request)
    {
        // @TODO: Deploy only requested file in developer mode without full regeneration, transform this to dataObject?
        $this->staticContent->deploy($request->getView());
        $content = $this->fileManager->readFile(BP . '/pub' . $request->getPath());
        $extension = pathinfo($request->getPath(), PATHINFO_EXTENSION);
        $contentTypeHeader = [];

        if (isset(self::MIME_TYPES[$extension])) {
            $contentTypeHeader = ['Content-Type' => self::MIME_TYPES[$extension]];
        }

        return new Response($content, Response::SUCCESS_STATUS_CODE, $contentTypeHeader);
    }
}
