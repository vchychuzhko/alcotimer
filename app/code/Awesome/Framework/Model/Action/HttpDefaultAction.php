<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Action;

use Awesome\Framework\Model\AppState;
use Awesome\Framework\Model\Http\Context;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Result\Response;

class HttpDefaultAction extends \Awesome\Framework\Model\AbstractAction
{
    private const FORBIDDEN_PAGE_PATH = '/pub/pages/403.html';
    private const NOTFOUND_PAGE_PATH = '/pub/pages/404.html';

    /**
     * @var AppState $appState
     */
    private $appState;

    /**
     * HttpDefaultAction constructor.
     * @param AppState $appState
     * @param Context $context
     */
    public function __construct(AppState $appState, Context $context)
    {
        parent::__construct($context);
        $this->appState = $appState;
    }

    /**
     * Return notfound or forbidden response in case no action was found.
     * @inheritDoc
     */
    public function execute(Request $request): Response
    {
        if ($request->getRedirectStatusCode() === Request::FORBIDDEN_REDIRECT_CODE
            && $this->appState->showForbidden()
        ) {
            if ($request->getAcceptType() === Request::JSON_ACCEPT_HEADER) {
                $response = $this->jsonResponseFactory->create()
                    ->setContentJson([
                        'status' => 'FORBIDDEN',
                        'message' => 'Requested path is not allowed.',
                    ])
                    ->setStatusCode(Response::FORBIDDEN_STATUS_CODE);
            } elseif ($request->getAcceptType() === Request::HTML_ACCEPT_HEADER && $content = $this->getForbiddenPage()) {
                $response = $this->htmlResponseFactory->create()
                    ->setContent($content)
                    ->setStatusCode(Response::FORBIDDEN_STATUS_CODE);
            } else {
                $response = $this->responseFactory->create()
                    ->setStatusCode(Response::FORBIDDEN_STATUS_CODE);
            }
        } else {
            if ($request->getAcceptType() === Request::JSON_ACCEPT_HEADER) {
                $response = $this->jsonResponseFactory->create()
                    ->setContentJson([
                        'status' => 'NOTFOUND',
                        'message' => 'Requested path was not found.',
                    ])
                    ->setStatusCode(Response::NOTFOUND_STATUS_CODE);
            } elseif ($request->getAcceptType() === Request::HTML_ACCEPT_HEADER && $content = $this->getNotfoundPage()) {
                $response = $this->htmlResponseFactory->create()
                    ->setContent($content)
                    ->setStatusCode(Response::NOTFOUND_STATUS_CODE);
            } else {
                $response = $this->responseFactory->create()
                    ->setStatusCode(Response::NOTFOUND_STATUS_CODE);
            }
        }

        return $response;
    }

    /**
     * Get 403 forbidden page content.
     * @return string|null
     */
    private function getForbiddenPage(): ?string
    {
        return @file_get_contents(BP . self::FORBIDDEN_PAGE_PATH) ?: null;
    }

    /**
     * Get 404 notfound page content.
     * @return string|null
     */
    private function getNotfoundPage(): ?string
    {
        return @file_get_contents(BP . self::NOTFOUND_PAGE_PATH) ?: null;
    }
}
