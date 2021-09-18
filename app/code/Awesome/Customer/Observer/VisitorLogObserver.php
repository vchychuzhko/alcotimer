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
    private $visitLogger;

    /**
     * VisitLogObserver constructor.
     * @param Config $config
     * @param VisitorLogger $visitorLogger
     */
    public function __construct(
        Config $config,
        VisitorLogger $visitorLogger
    ) {
        $this->config = $config;
        $this->visitLogger = $visitorLogger;
    }

    /**
     * Log visitor request information.
     * @inheritDoc
     */
    public function execute(Event $event): void
    {
        if ($this->isEnabled()) {
            /** @var Request $request */
            $request = $event->getRequest();

            $this->visitLogger->logVisit($request->getUserIp() . ' - ' . $request->getUrl());
        }
    }

    /**
     * Check if visitor logging is enabled.
     * @return bool
     */
    private function isEnabled(): bool
    {
        return (bool) $this->config->get(self::VISITOR_LOG_CONFIG_PATH);
    }
}