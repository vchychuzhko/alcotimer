<?php

namespace Awesome\Framework\Model\Event;

use Awesome\Framework\Model\Event;

interface ObserverInterface
{
    /**
     * Action to be called on event dispatching.
     * @param Event $event
     */
    public function execute($event);
}
