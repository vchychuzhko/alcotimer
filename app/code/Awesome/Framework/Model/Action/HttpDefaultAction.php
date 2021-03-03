<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Action;

use Awesome\Framework\Model\AppState;
use Awesome\Framework\Model\FileManager\PhpFileManager;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\ResponseInterface;
use Awesome\Framework\Model\Result\ResponseFactory;

class HttpDefaultAction extends \Awesome\Framework\Model\AbstractAction
{
    private const FORBIDDEN_PAGE_PATH = '/pub/pages/403.php';
    private const NOTFOUND_PAGE_PATH = '/pub/pages/404.php';

    /**
     * @var AppState $appState
     */
    private $appState;

    /**
     * @var PhpFileManager $phpFileManager
     */
    private $phpFileManager;

    /**
     * HttpDefaultAction constructor.
     * @param AppState $appState
     * @param PhpFileManager $phpFileManager
     * @param ResponseFactory $responseFactory
     */
    public function __construct(AppState $appState, PhpFileManager $phpFileManager, ResponseFactory $responseFactory)
    {
        parent::__construct($responseFactory);
        $this->appState = $appState;
        $this->phpFileManager = $phpFileManager;
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
                    ]);
            } elseif ($request->getAcceptType() === Request::HTML_ACCEPT_HEADER && $content = $this->getForbiddenPage()) {
                $response = $this->responseFactory->create(ResponseFactory::TYPE_HTML)
                    ->setContent($content);
            } else {
                $response = $this->responseFactory->create();
            }

            $response->setStatusCode(ResponseInterface::FORBIDDEN_STATUS_CODE);
        } else {
            if ($request->getAcceptType() === Request::JSON_ACCEPT_HEADER) {
                $response = $this->responseFactory->create(ResponseFactory::TYPE_JSON)
                    ->setData([
                        'status'  => 'NOTFOUND',
                        'message' => 'Requested path was not found.',
                    ]);
            } elseif ($request->getAcceptType() === Request::HTML_ACCEPT_HEADER && $content = $this->getNotfoundPage()) {
                $response = $this->responseFactory->create(ResponseFactory::TYPE_HTML)
                    ->setContent($content);
            } else {
                $response = $this->responseFactory->create();
            }

            $response->setStatusCode(ResponseInterface::NOTFOUND_STATUS_CODE);
        }

        return $response;
    }

    /**
     * Get 403 forbidden page content.
     * @return string|false
     */
    private function getForbiddenPage()
    {
        return $this->phpFileManager->includeFile(BP . self::FORBIDDEN_PAGE_PATH, true, true);
    }

    /**
     * Get 404 notfound page content.
     * @return string|false
     */
    private function getNotfoundPage()
    {
        return $this->phpFileManager->includeFile(BP . self::NOTFOUND_PAGE_PATH, true, true);
    }
}
