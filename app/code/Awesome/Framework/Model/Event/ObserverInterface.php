<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Event;

use Awesome\Framework\Model\Event;

interface ObserverInterface
{
    /**
     * Action to be called on event dispatching.
     * @param Event $event
     * @return void
     */
    public function execute(Event $event): void;
}
