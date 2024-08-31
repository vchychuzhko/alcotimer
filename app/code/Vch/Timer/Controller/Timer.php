<?php
declare(strict_types=1);

namespace Vch\Timer\Controller;

use Vch\Framework\Model\Http\Request;
use Vch\Framework\Model\ResponseInterface;

class Timer extends \Vch\Frontend\Model\AbstractPageAction
{
    protected string $pageLayout = 'timer_index_index';

    /**
     * @inheritDoc
     */
    public function execute(Request $request): ResponseInterface
    {
        $this->getPageConfig()->setTitle(__('AlcoTimer'))
            ->setDescription(__('Web App for people, who would like to make drinking process become really challenging.'))
            ->setKeywords('AlcoTimer,Alcohol,Timer,Drink,Party,Games');

        return $this->createPageResponse();
    }
}
