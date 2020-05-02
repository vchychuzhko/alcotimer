<?php

namespace Awesome\Framework\Model\Event;

use Awesome\Framework\Model\Event;
use Awesome\Framework\Model\Event\ObserverInterface;
use Awesome\Framework\Model\XmlParser\Event as EventXmlParser;

class Manager
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
     */
    public function dispatch($eventName, $data = [])
    {
        if ($observers = $this->eventXmlParser->getObservers($eventName)) {
            $event = new Event(array_merge(['event_name' => $eventName], $data));

            foreach ($observers as $observer) {
                /** @var ObserverInterface $observer */
                $observer = new $observer();
                $observer->execute($event);
            }
        }
    }
}
