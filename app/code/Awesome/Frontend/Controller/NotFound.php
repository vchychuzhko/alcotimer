<?php
declare(strict_types=1);

namespace Awesome\Frontend\Controller;

use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\ResponseInterface;

class NotFound extends \Awesome\Frontend\Model\AbstractPageAction
{
    protected const PAGE_LAYOUT = 'notfound_index_index';

    /**
     * @inheritDoc
     */
    public function execute(Request $request): ResponseInterface
    {
        // @TODO: Add noindex,nofollow
        return $this->responseFactory->createPage(self::PAGE_LAYOUT)
            ->setStatusCode(ResponseInterface::NOTFOUND_STATUS_CODE);
    }
}
