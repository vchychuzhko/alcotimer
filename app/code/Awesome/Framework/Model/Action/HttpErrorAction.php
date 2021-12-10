<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Action;

use Awesome\Framework\Model\ResponseInterface;
use Awesome\Framework\Model\Result\Response;
use Awesome\Framework\Model\Http\Request;

class HttpErrorAction
{
    private const INTERNALERROR_PAGE_PATH = '/pub/pages/500.php';

    /**
     * @var string $errorMessage
     */
    private $errorMessage;

    /**
     * @var bool $isDeveloperMode
     */
    private $isDeveloperMode;

    /**
     * @var string|null $acceptType
     */
    private $acceptType;

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
        if ($this->acceptType === Request::JSON_ACCEPT_HEADER) {
            $response = new Response(
                json_encode([
                    'status' => 'ERROR',
                    'message' => $this->isDeveloperMode
                        ? $this->errorMessage
                        : 'An internal error occurred. Details are hidden and can be found in logs files.',
                ]),
                ResponseInterface::INTERNALERROR_STATUS_CODE,
                ['Content-Type' => 'application/json']
            );
        } elseif ($this->acceptType === Request::HTML_ACCEPT_HEADER && $content = $this->getInternalErrorPage()) {
            $response = new Response(
                $this->isDeveloperMode ? '<pre>' . $this->errorMessage . '</pre>' : $content,
                ResponseInterface::INTERNALERROR_STATUS_CODE,
                ['Content-Type' => 'text/html']
            );
        } else {
            $response = new Response('', ResponseInterface::INTERNALERROR_STATUS_CODE);
        }

        return $response;
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
