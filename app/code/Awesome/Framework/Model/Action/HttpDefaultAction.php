<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Action;

use Awesome\Framework\Model\AppState;
use Awesome\Framework\Model\Http\Response;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Response\HtmlResponse;
use Awesome\Framework\Model\Http\Response\JsonResponse;

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
     */
    public function __construct(AppState $appState)
    {
        parent::__construct();
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
                $response = new JsonResponse(
                    [
                        'status' => 'FORBIDDEN',
                        'message' => 'Requested path is not allowed.',
                    ],
                    Response::FORBIDDEN_STATUS_CODE
                );
            } elseif ($request->getAcceptType() === Request::HTML_ACCEPT_HEADER && $content = $this->getForbiddenPage()) {
                $response = new HtmlResponse($content, Response::FORBIDDEN_STATUS_CODE);
            } else {
                $response = new Response('', Response::FORBIDDEN_STATUS_CODE);
            }
        } else {
            if ($request->getAcceptType() === Request::JSON_ACCEPT_HEADER) {
                $response = new JsonResponse(
                    [
                        'status' => 'NOTFOUND',
                        'message' => 'Requested path was not found.',
                    ],
                    Response::NOTFOUND_STATUS_CODE
                );
            } elseif ($request->getAcceptType() === Request::HTML_ACCEPT_HEADER && $content = $this->getNotfoundPage()) {
                $response = new HtmlResponse($content, Response::NOTFOUND_STATUS_CODE);
            } else {
                $response = new Response('', Response::NOTFOUND_STATUS_CODE);
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
