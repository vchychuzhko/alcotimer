<?php

namespace Awesome\Framework\Model\Event;

use Awesome\Framework\Model\Event;
use Awesome\Framework\Model\Event\ObserverInterface;
use Awesome\Framework\Model\XmlParser\EventXmlParser;

class EventManager
{
    /**
     * @var EventXmlParser $eventXmlParser
     */
    private $eventXmlParser;

    /**
     * EventManager constructor.
     */
    public function __construct()
    {
        $this->eventXmlParser = new EventXmlParser();
    }

    /**
     * Fire event with calling all related observers.
     * @param string $eventName
     * @param array $data
     * @throws \LogicException
     */
    public function dispatch($eventName, $data = [])
    {
        if ($observers = $this->eventXmlParser->getObservers($eventName)) {
            $event = new Event($eventName, $data);

            foreach ($observers as $observer) {
                /** @var ObserverInterface $observer */
                $observer = new $observer();

                if (!($observer instanceof ObserverInterface)) {
                    throw new \LogicException(sprintf('Observer "%s" does not implement ObserverInterface', get_class($observer)));
                }
                $observer->execute($event);
            }
        }
    }
}
