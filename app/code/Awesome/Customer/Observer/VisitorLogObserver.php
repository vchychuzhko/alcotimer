<?php
declare(strict_types=1);

namespace Awesome\Customer\Observer;

use Awesome\Customer\Model\VisitorLogger;
use Awesome\Framework\Model\Event;
use Awesome\Framework\Model\Http\Request;

class VisitorLogObserver implements \Awesome\Framework\Model\Event\ObserverInterface
{
    /**
     * @var VisitorLogger $visitorLogger
     */
    private $visitorLogger;

    /**
     * VisitorLogObserver constructor.
     * @param VisitorLogger $visitorLogger
     */
    public function __construct(VisitorLogger $visitorLogger)
    {
        $this->visitorLogger = $visitorLogger;
    }

    /**
     * Resolve controller routing.
     * @inheritDoc
     */
    public function execute(Event $event): void
    {
        /** @var Request $request */
        $request = $event->getRequest();

        $this->visitorLogger->logVisitor($request->getUserIp() . ' - ' . $request->getUrl());
    }
}
