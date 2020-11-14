<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Action;

use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http\Response;
use Awesome\Framework\Model\Http\Response\HtmlResponse;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Response\JsonResponse;

class MaintenanceAction extends \Awesome\Framework\Model\AbstractAction
{
    private const MAINTENANCE_PAGE_PATH = '/pub/pages/maintenance.html';

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * MaintenanceAction constructor.
     * @param FileManager $fileManager
     */
    public function __construct(FileManager $fileManager)
    {
        parent::__construct();
        $this->fileManager = $fileManager;
    }

    /**
     * Show maintenance response according to the accept type.
     * @inheritDoc
     */
    public function execute(Request $request): Response
    {
        if ($request->getAcceptType() === Request::JSON_ACCEPT_HEADER) {
            $response = new JsonResponse(
                [
                    'status' => 'MAINTENANCE',
                    'message' => 'Service is unavailable due to maintenance works.',
                ],
                Response::INTERNAL_ERROR_STATUS_CODE
            );
        } elseif ($request->getAcceptType() === Request::HTML_ACCEPT_HEADER && $content = $this->getMaintenancePage()) {
            $response = new HtmlResponse($content, Response::SERVICE_UNAVAILABLE_STATUS_CODE);
        } else {
            $response = new Response('', Response::SERVICE_UNAVAILABLE_STATUS_CODE);
        }

        return $response;
    }

    /**
     * Get maintenance page content.
     * @return string
     */
    private function getMaintenancePage(): string
    {
        return $this->fileManager->readFile(BP . self::MAINTENANCE_PAGE_PATH, false);
    }
}
