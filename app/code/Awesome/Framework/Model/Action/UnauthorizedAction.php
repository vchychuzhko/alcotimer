<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Action;

use Awesome\Framework\Model\FileManager\PhpFileManager;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\ResponseFactory;
use Awesome\Framework\Model\ResponseInterface;

class UnauthorizedAction extends \Awesome\Framework\Model\AbstractAction
{
    private const UNAUTHORIZED_PAGE_PATH = '/pub/pages/401.php';

    private PhpFileManager $phpFileManager;

    /**
     * UnauthorizedAction constructor.
     * @param PhpFileManager $phpFileManager
     * @param ResponseFactory $responseFactory
     */
    public function __construct(PhpFileManager $phpFileManager, ResponseFactory $responseFactory)
    {
        parent::__construct($responseFactory);
        $this->phpFileManager = $phpFileManager;
    }

    /**
     * Show unauthorized response according to the accept type.
     * @inheritDoc
     */
    public function execute(Request $request): ResponseInterface
    {
        if ($request->getAcceptType() === Request::ACCEPT_HEADER_JSON) {
            $response = $this->responseFactory->create(ResponseFactory::TYPE_JSON)
                ->setData([
                    'message' => "Request's authorization failed.",
                ]);
        } elseif ($request->getAcceptType() === Request::ACCEPT_HEADER_HTML && $content = $this->getUnauthorizedPage()) {
            $response = $this->responseFactory->create(ResponseFactory::TYPE_HTML)
                ->setContent($content);
        } else {
            $response = $this->responseFactory->create();
        }

        return $response->setStatusCode(ResponseInterface::UNAUTHORIZED_STATUS_CODE);
    }

    /**
     * Get unauthorized page content.
     * @return string
     */
    private function getUnauthorizedPage(): string
    {
        return $this->phpFileManager->includeFile(BP . self::UNAUTHORIZED_PAGE_PATH, true, true);
    }
}
