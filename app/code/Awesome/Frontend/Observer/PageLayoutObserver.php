<?php
declare(strict_types=1);

namespace Awesome\Frontend\Observer;

use Awesome\Framework\Model\Event;
use Awesome\Framework\Model\Http\ActionResolver;
use Awesome\Framework\Model\Http\Request;
use Awesome\Frontend\Model\Action\LayoutHandler;

class PageLayoutObserver implements \Awesome\Framework\Model\Event\ObserverInterface
{
    /**
     * Add layout renderer as Http action.
     * @inheritDoc
     */
    public function execute(Event $event): void
    {
        /** @var ActionResolver $router */
        $actionResolver = $event->getActionResolver();

        $actionResolver->addAction(LayoutHandler::class);
    }
}
