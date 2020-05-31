<?php

namespace Awesome\Frontend\Observer;

use Awesome\Framework\Model\Http\Router;
use Awesome\Frontend\Model\Action\LayoutHandler;

class PageLayoutObserver implements \Awesome\Framework\Model\Event\ObserverInterface
{
    /**
     * Add layout renderer as a http action.
     * @inheritDoc
     */
    public function execute($event)
    {
        /** @var Router $router */
        $router = $event->getRouter();
        $router->addAction(new LayoutHandler());
    }
}
