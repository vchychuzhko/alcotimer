<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Action;

use Awesome\Framework\Model\FileManager\PhpFileManager;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\ResponseFactory;
use Awesome\Framework\Model\ResponseInterface;

class HttpDefaultAction extends \Awesome\Framework\Model\AbstractAction
{
    private const NOTFOUND_PAGE = '/pub/pages/404.php';

    private PhpFileManager $phpFileManager;

    /**
     * HttpDefaultAction constructor.
     * @param PhpFileManager $phpFileManager
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        PhpFileManager $phpFileManager,
        ResponseFactory $responseFactory
    ) {
        parent::__construct($responseFactory);
        $this->phpFileManager = $phpFileManager;
    }

    /**
     * Return notfound or forbidden response in case no action was found.
     * @inheritDoc
     */
    public function execute(Request $request): ResponseInterface
    {
        if ($request->getAcceptType() === Request::ACCEPT_HEADER_JSON) {
            $response = $this->responseFactory->create(ResponseFactory::TYPE_JSON)
                ->setData(['message' => 'Requested path was not found']);
        } elseif ($request->getAcceptType() === Request::ACCEPT_HEADER_HTML && $content = $this->getNotfoundPage()) {
            $response = $this->responseFactory->create(ResponseFactory::TYPE_HTML)
                ->setContent($content);
        } else {
            $response = $this->responseFactory->create();
        }

        return $response->setStatusCode(ResponseInterface::NOTFOUND_STATUS_CODE);
    }

    /**
     * Get 404 notfound page content.
     * @return string
     */
    private function getNotfoundPage(): string
    {
        return $this->phpFileManager->includeFile(BP . self::NOTFOUND_PAGE, true, true);
    }
}
