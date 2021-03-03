<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Action;

use Awesome\Framework\Model\FileManager\PhpFileManager;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\ResponseInterface;
use Awesome\Framework\Model\Result\ResponseFactory;

class MaintenanceAction extends \Awesome\Framework\Model\AbstractAction
{
    private const MAINTENANCE_PAGE_PATH = '/pub/pages/maintenance.php';

    /**
     * @var PhpFileManager $phpFileManager
     */
    private $phpFileManager;

    /**
     * MaintenanceAction constructor.
     * @param PhpFileManager $phpFileManager
     * @param ResponseFactory $responseFactory
     */
    public function __construct(PhpFileManager $phpFileManager, ResponseFactory $responseFactory)
    {
        parent::__construct($responseFactory);
        $this->phpFileManager = $phpFileManager;
    }

    /**
     * Show maintenance response according to the accept type.
     * @inheritDoc
     */
    public function execute(Request $request): ResponseInterface
    {
        if ($request->getAcceptType() === Request::JSON_ACCEPT_HEADER) {
            $response = $this->responseFactory->create(ResponseFactory::TYPE_JSON)
                ->setData([
                    'status'  => 'MAINTENANCE',
                    'message' => 'Service is unavailable due to maintenance works',
                ])
                ->setStatusCode(ResponseInterface::INTERNAL_ERROR_STATUS_CODE);
        } elseif ($request->getAcceptType() === Request::HTML_ACCEPT_HEADER && $content = $this->getMaintenancePage()) {
            $response = $this->responseFactory->create(ResponseFactory::TYPE_HTML)
                ->setContent($content)
                ->setStatusCode(ResponseInterface::SERVICE_UNAVAILABLE_STATUS_CODE);
        } else {
            $response = $this->responseFactory->create()
                ->setStatusCode(ResponseInterface::SERVICE_UNAVAILABLE_STATUS_CODE);
        }

        return $response;
    }

    /**
     * Get maintenance page content.
     * @return string
     */
    private function getMaintenancePage(): string
    {
        return $this->phpFileManager->includeFile(BP . self::MAINTENANCE_PAGE_PATH, true, true);
    }
}
