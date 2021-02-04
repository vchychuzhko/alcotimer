<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Action;

use Awesome\Framework\Model\AppState;
use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\ResponseInterface;
use Awesome\Framework\Model\Result\ResponseFactory;

class HttpDefaultAction extends \Awesome\Framework\Model\AbstractAction
{
    private const FORBIDDEN_PAGE_PATH = '/pub/pages/403.html';
    private const NOTFOUND_PAGE_PATH = '/pub/pages/404.html';

    /**
     * @var AppState $appState
     */
    private $appState;

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * HttpDefaultAction constructor.
     * @param AppState $appState
     * @param FileManager $fileManager
     * @param ResponseFactory $responseFactory
     */
    public function __construct(AppState $appState, FileManager $fileManager, ResponseFactory $responseFactory)
    {
        parent::__construct($responseFactory);
        $this->appState = $appState;
        $this->fileManager = $fileManager;
    }

    /**
     * Return notfound or forbidden response in case no action was found.
     * @inheritDoc
     */
    public function execute(Request $request): ResponseInterface
    {
        if ($request->getRedirectStatusCode() === Request::FORBIDDEN_REDIRECT_CODE
            && $this->appState->showForbidden()
        ) {
            if ($request->getAcceptType() === Request::JSON_ACCEPT_HEADER) {
                $response = $this->responseFactory->create(ResponseFactory::TYPE_JSON)
                    ->setData([
                        'status'  => 'FORBIDDEN',
                        'message' => 'Requested path is not allowed.',
                    ])
                    ->setStatusCode(ResponseInterface::FORBIDDEN_STATUS_CODE);
            } elseif ($request->getAcceptType() === Request::HTML_ACCEPT_HEADER && $content = $this->getForbiddenPage()) {
                $response = $this->responseFactory->create(ResponseFactory::TYPE_HTML)
                    ->setContent($content)
                    ->setStatusCode(ResponseInterface::FORBIDDEN_STATUS_CODE);
            } else {
                $response = $this->responseFactory->create()
                    ->setStatusCode(ResponseInterface::FORBIDDEN_STATUS_CODE);
            }
        } else {
            if ($request->getAcceptType() === Request::JSON_ACCEPT_HEADER) {
                $response = $this->responseFactory->create(ResponseFactory::TYPE_JSON)
                    ->setData([
                        'status'  => 'NOTFOUND',
                        'message' => 'Requested path was not found.',
                    ])
                    ->setStatusCode(ResponseInterface::NOTFOUND_STATUS_CODE);
            } elseif ($request->getAcceptType() === Request::HTML_ACCEPT_HEADER && $content = $this->getNotfoundPage()) {
                $response = $this->responseFactory->create(ResponseFactory::TYPE_HTML)
                    ->setContent($content)
                    ->setStatusCode(ResponseInterface::NOTFOUND_STATUS_CODE);
            } else {
                $response = $this->responseFactory->create()
                    ->setStatusCode(ResponseInterface::NOTFOUND_STATUS_CODE);
            }
        }

        return $response;
    }

    /**
     * Get 403 forbidden page content.
     * @return string|false
     */
    private function getForbiddenPage()
    {
        return $this->fileManager->readFile(BP . self::FORBIDDEN_PAGE_PATH, true);
    }

    /**
     * Get 404 notfound page content.
     * @return string|false
     */
    private function getNotfoundPage()
    {
        return $this->fileManager->readFile(BP . self::NOTFOUND_PAGE_PATH, true);
    }
}
