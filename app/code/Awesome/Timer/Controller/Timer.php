<?php
declare(strict_types=1);

namespace Awesome\Timer\Controller;

use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\ResponseInterface;
use Awesome\Frontend\Model\Result\ResultPageFactory;

class Timer implements \Awesome\Framework\Model\ActionInterface
{
    private const PAGE_LAYOUT = 'timer_index_index';

    private ResultPageFactory $resultPageFactory;

    /**
     * Timer constructor.
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
        // @TODO: Add AbstractPageController
        return $this->resultPageFactory->create(self::PAGE_LAYOUT);
    }
}
