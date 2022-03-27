<?php
declare(strict_types=1);

namespace Awesome\Frontend\Controller;

use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\ResponseInterface;
use Awesome\Frontend\Model\Page\PageConfig;

class NotFound extends \Awesome\Frontend\Model\AbstractPageAction
{
    protected string $pageLayout = 'notfound_index_index';

    /**
     * @inheritDoc
     */
    public function execute(Request $request): ResponseInterface
    {
        $this->getPageConfig()->setTitle(__('Page Not Found'))
            ->setRobots(PageConfig::NOINDEX_NOFOLLOW_ROBOTS);

        return $this->createPageResponse()
            ->setStatusCode(ResponseInterface::NOTFOUND_STATUS_CODE);
    }
}
