<?php
declare(strict_types=1);

namespace Awesome\Timer\Controller;

use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\ResponseInterface;

class Timer extends \Awesome\Frontend\Model\AbstractPageAction
{
    protected const PAGE_LAYOUT = 'timer_index_index';

    /**
     * @inheritDoc
     */
    public function execute(Request $request): ResponseInterface
    {
        return $this->responseFactory->createPage(self::PAGE_LAYOUT);
    }
}
