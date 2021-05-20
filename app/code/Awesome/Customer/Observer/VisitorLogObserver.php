<?php
declare(strict_types=1);

namespace Awesome\Customer\Observer;

use Awesome\Customer\Model\VisitorLogger;
use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Event;
use Awesome\Framework\Model\Http\Request;

class VisitorLogObserver implements \Awesome\Framework\Model\Event\ObserverInterface
{
    private const VISITOR_LOG_CONFIG_PATH = 'visitor_log';

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var VisitorLogger $visitorLogger
     */
    private $visitorLogger;

    /**
     * VisitorLogObserver constructor.
     * @param Config $config
     * @param VisitorLogger $visitorLogger
     */
    public function __construct(Config $config, VisitorLogger $visitorLogger)
    {
        $this->config = $config;
        $this->visitorLogger = $visitorLogger;
    }

    /**
     * Resolve controller routing.
     * @inheritDoc
     */
    public function execute(Event $event): void
    {
        if ($this->config->get(self::VISITOR_LOG_CONFIG_PATH)) {
            /** @var Request $request */
            $request = $event->getRequest();

            $this->visitorLogger->logVisitor($request->getUserIp() . ' - ' . $request->getUrl());
        }
    }
}