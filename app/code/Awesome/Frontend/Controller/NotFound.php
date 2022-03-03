<?php
declare(strict_types=1);

namespace Awesome\Frontend\Controller;

use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\ResponseInterface;
use Awesome\Frontend\Model\Result\ResultPageFactory;

class NotFound implements \Awesome\Framework\Model\ActionInterface
{
    private const PAGE_LAYOUT = 'notfound_index_index';

    private ResultPageFactory $resultPageFactory;

    /**
     * NotFound constructor.
     * @param ResultPageFactory $resultPageFactory
     */
    public function __construct(
        ResultPageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(Request $request): ResponseInterface
    {
        // @TODO: Add noindex,nofollow
        // @TODO: Add AbstractPageController
        return $this->resultPageFactory->create(self::PAGE_LAYOUT)
            ->setStatusCode(ResponseInterface::NOTFOUND_STATUS_CODE);
    }
}
