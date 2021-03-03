<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\Action;

use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\ResponseInterface;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Result\ResponseFactory;
use Awesome\Frontend\Model\StaticContent;

/**
 * Class StaticGenerationHandler
 * @method string getRequestedFile()
 */
class StaticGenerationHandler extends \Awesome\Framework\Model\AbstractAction
{
    private const STATIC_FILE_PATTERN = '/^(\/static\/)(version.+?\/)?(%s|%s)\/(.*)$/';

    /**
     * Mime types for static files.
     */
    private const MIME_TYPES = [
        'css'   => 'text/css',
        'html'  => 'text/html',
        'js'    => 'application/javascript',
        'json'  => 'application/json',
        'eot'   => 'application/vnd.ms-fontobject',
        'ttf'   => 'application/x-font-ttf',
        'otf'   => 'application/x-font-otf',
        'woff'  => 'application/x-font-woff',
        'woff2' => 'application/font-woff2',
    ];

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
     * @param FileManager $fileManager
     * @param ResponseFactory $responseFactory
     * @param StaticContent $staticContent
     * @param array $data
     */
    public function __construct(
        FileManager $fileManager,
        ResponseFactory $responseFactory,
        StaticContent $staticContent,
        array $data = []
    ) {
        parent::__construct($responseFactory, $data);
        $this->fileManager = $fileManager;
        $this->staticContent = $staticContent;
    }

    /**
     * Generate static files and return content for requested one.
     * @inheritDoc
     * @throws \Exception
     */
    public function execute(Request $request): ResponseInterface
    {
        $path = $this->getRequestedFile();
        $view = preg_replace(
            sprintf(self::STATIC_FILE_PATTERN, Http::FRONTEND_VIEW, Http::BACKEND_VIEW), '$3', $request->getPath()
        );

        $this->staticContent->deployFile($path, $view);

        $staticPath = preg_replace(
            sprintf(self::STATIC_FILE_PATTERN, Http::FRONTEND_VIEW, Http::BACKEND_VIEW), '$4', $request->getPath()
        );

        $content = $this->fileManager->readFile(BP . StaticContent::STATIC_FOLDER_PATH . $view . '/' . $staticPath);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $headers = [];

        if (isset(self::MIME_TYPES[$extension])) {
            $headers = ['Content-Type' => self::MIME_TYPES[$extension]];
        }

        return $this->responseFactory->create()
            ->setContent($content)
            ->setHeaders($headers);
    }
}
