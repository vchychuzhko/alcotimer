<?php
declare(strict_types=1);

namespace Vch\Framework\Model\Action;

use Vch\Framework\Model\FileManager\PhpFileManager;
use Vch\Framework\Model\Http\Request;
use Vch\Framework\Model\Http\ResponseFactory;
use Vch\Framework\Model\ResponseInterface;

class MaintenanceAction extends \Vch\Framework\Model\AbstractAction
{
    private const MAINTENANCE_PAGE_PATH = '/pub/pages/503.php';

    private PhpFileManager $phpFileManager;

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
        if ($request->getAcceptType() === Request::ACCEPT_HEADER_JSON) {
            $response = $this->responseFactory->create(ResponseFactory::TYPE_JSON)
                ->setData([
                    'message' => 'Service is unavailable due to maintenance works.',
                ]);
        } elseif ($request->getAcceptType() === Request::ACCEPT_HEADER_HTML && $content = $this->getMaintenancePage()) {
            $response = $this->responseFactory->create(ResponseFactory::TYPE_HTML)
                ->setContent($content);
        } else {
            $response = $this->responseFactory->create();
        }

        return $response->setStatusCode(ResponseInterface::SERVICEUNAVAILABLE_STATUS_CODE);
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
