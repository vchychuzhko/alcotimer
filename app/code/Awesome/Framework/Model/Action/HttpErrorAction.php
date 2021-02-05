<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Action;

use Awesome\Framework\Model\ResponseInterface;
use Awesome\Framework\Model\Result\Response;
use Awesome\Framework\Model\Http\Request;

/**
 * Class HttpErrorAction
 * @method string|null getAcceptType()
 * @method string getErrorMessage()
 * @method bool getIsDeveloperMode()
 */
class HttpErrorAction extends \Awesome\Framework\Model\DataObject
{
    private const INTERNALERROR_PAGE_PATH = '/pub/pages/internal_error.html';

    /**
     * Show internal error response according to accept type.
     * @return Response
     */
    public function execute(): Response
    {
        $errorMessage = $this->getErrorMessage();

        if ($this->getAcceptType() === Request::JSON_ACCEPT_HEADER) {
            $response = new Response(
                json_encode([
                    'status' => 'ERROR',
                    'message' => $this->getIsDeveloperMode()
                        ? $errorMessage
                        : 'Internal error occurred. Details are hidden and can be found in logs files.',
                ]),
                ResponseInterface::INTERNAL_ERROR_STATUS_CODE,
                ['Content-Type' => 'application/json']
            );
        } elseif ($this->getAcceptType() === Request::HTML_ACCEPT_HEADER && $content = $this->getInternalErrorPage()) {
            $response = new Response(
                $this->getIsDeveloperMode() ? '<pre>' . $errorMessage . '</pre>' : $content,
                ResponseInterface::INTERNAL_ERROR_STATUS_CODE,
                ['Content-Type' => 'text/html']
            );
        } else {
            $response = new Response('', ResponseInterface::INTERNAL_ERROR_STATUS_CODE);
        }

        return $response;
    }

    /**
     * Get internal error page content.
     * @return string|null
     */
    private function getInternalErrorPage(): ?string
    {
        return @file_get_contents(BP . self::INTERNALERROR_PAGE_PATH) ?: null;
    }
}
