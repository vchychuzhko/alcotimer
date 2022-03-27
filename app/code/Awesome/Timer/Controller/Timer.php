<?php
declare(strict_types=1);

namespace Awesome\Timer\Controller;

use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\ResponseInterface;

class Timer extends \Awesome\Frontend\Model\AbstractPageAction
{
    protected string $pageLayout = 'timer_index_index';

    /**
     * @inheritDoc
     */
    public function execute(Request $request): ResponseInterface
    {
        $this->getPageConfig()->setTitle(__('AlcoTimer'))
            ->setDescription(__('Web App for people, who would like to make drinking process become really challenging.'))
            ->setKeywords('AlcoTimer,Alco,Timer,Drink'); // @todo: translation?

        return $this->createPageResponse();
    }
}
