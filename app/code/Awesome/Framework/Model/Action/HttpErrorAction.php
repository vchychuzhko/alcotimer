<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Action;

use Awesome\Framework\Model\ResponseInterface;
use Awesome\Framework\Model\Http\Response;
use Awesome\Framework\Model\Http\Request;

class HttpErrorAction
{
    private const INTERNALERROR_PAGE_PATH = '/pub/pages/500.php';

    private string $errorMessage;

    private bool $isDeveloperMode;

    private ?string $acceptType;

    /**
     * HttpErrorAction constructor.
     * @param string $errorMessage
     * @param bool $isDeveloperMode
     * @param string|null $acceptType
     */
    public function __construct(string $errorMessage, bool $isDeveloperMode = false, ?string $acceptType = null)
    {
        $this->errorMessage = $errorMessage;
        $this->isDeveloperMode = $isDeveloperMode;
        $this->acceptType = $acceptType;
    }

    /**
     * Show internal error response according to accept type.
     * @return Response
     */
    public function execute(): Response
    {
        $response = new Response();

        if ($this->acceptType === Request::ACCEPT_HEADER_JSON) {
            $response->setContent(json_encode([
                'message' => $this->isDeveloperMode
                    ? $this->errorMessage
                    : 'An internal error occurred. Details can be found in logs files.',
            ]))
                ->setHeader('Content-Type', 'application/json');
        } elseif ($this->acceptType === Request::ACCEPT_HEADER_HTML && $content = $this->getInternalErrorPage()) {
            $response->setContent($this->isDeveloperMode ? '<pre>' . $this->errorMessage . '</pre>' : $content)
                ->setHeader('Content-Type', 'application/json');
        }

        return $response->setStatusCode(ResponseInterface::INTERNALERROR_STATUS_CODE);
    }

    /**
     * Get internal error page content.
     * @return string|null
     */
    private function getInternalErrorPage(): ?string
    {
        if (is_file(BP . self::INTERNALERROR_PAGE_PATH)) {
            ob_start();
            include BP . self::INTERNALERROR_PAGE_PATH;

            return ob_get_clean();
        }

        return null;
    }
}
