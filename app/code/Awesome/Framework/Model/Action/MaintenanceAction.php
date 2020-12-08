<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Action;

use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http\Context;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Result\Response;

class MaintenanceAction extends \Awesome\Framework\Model\AbstractAction
{
    private const MAINTENANCE_PAGE_PATH = '/pub/pages/maintenance.html';

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * MaintenanceAction constructor.
     * @param Context $context
     * @param FileManager $fileManager
     */
    public function __construct(Context $context, FileManager $fileManager)
    {
        parent::__construct($context);
        $this->fileManager = $fileManager;
    }

    /**
     * Show maintenance response according to the accept type.
     * @inheritDoc
     */
    public function execute(Request $request): Response
    {
        if ($request->getAcceptType() === Request::JSON_ACCEPT_HEADER) {
            $response = $this->jsonResponseFactory->create()
                ->setContentJson([
                    'status' => 'MAINTENANCE',
                    'message' => 'Service is unavailable due to maintenance works.',
                ])
                ->setStatusCode(Response::INTERNAL_ERROR_STATUS_CODE);
        } elseif ($request->getAcceptType() === Request::HTML_ACCEPT_HEADER && $content = $this->getMaintenancePage()) {
            $response = $this->htmlResponseFactory->create()
                ->setContent($content)
                ->setStatusCode(Response::SERVICE_UNAVAILABLE_STATUS_CODE);
        } else {
            $response = $this->responseFactory->create()
                ->setStatusCode(Response::SERVICE_UNAVAILABLE_STATUS_CODE);
        }

        return $response;
    }

    /**
     * Get maintenance page content.
     * @return string
     */
    private function getMaintenancePage(): string
    {
        return $this->fileManager->readFile(BP . self::MAINTENANCE_PAGE_PATH);
    }
}
