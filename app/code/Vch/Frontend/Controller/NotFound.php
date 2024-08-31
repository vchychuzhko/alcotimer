<?php
declare(strict_types=1);

namespace Vch\Frontend\Controller;

use Vch\Framework\Model\Http\Request;
use Vch\Framework\Model\ResponseInterface;
use Vch\Frontend\Model\Page\PageConfig;

class NotFound extends \Vch\Frontend\Model\AbstractPageAction
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
