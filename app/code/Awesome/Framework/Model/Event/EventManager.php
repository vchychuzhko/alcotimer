<?php

namespace Awesome\Framework\Model\Event;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\Event;
use Awesome\Framework\Model\Event\ObserverInterface;
use Awesome\Framework\Model\XmlParser\EventXmlParser;

class EventManager
{
    private const EVENTS_CACHE_TAG = 'events';

    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var EventXmlParser $eventXmlParser
     */
    private $eventXmlParser;

    /**
     * EventManager constructor.
     * @param Cache $cache
     * @param EventXmlParser $eventXmlParser
     */
    public function __construct(Cache $cache, EventXmlParser $eventXmlParser)
    {
        $this->cache = $cache;
        $this->eventXmlParser = $eventXmlParser;
    }

    /**
     * Fire event with calling all related observers.
     * @param string $eventName
     * @param array $data
     * @throws \LogicException
     */
    public function dispatch($eventName, $data = [])
    {
        if ($observers = $this->getObservers($eventName)) {
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

    /**
     * Get observers for provided event name.
     * @param string $eventName
     * @return array
     * @throws \LogicException
     */
    public function getObservers($eventName)
    {
        $eventsData = $this->getEventsData();

        return $eventsData[$eventName] ?? null;
    }

    /**
     * Get all declared events.
     * @return array
     * @throws \LogicException
     */
    public function getEvents()
    {
        return array_keys($this->getEventsData());
    }

    /**
     * Get events data.
     * @return array
     * @throws \LogicException
     */
    private function getEventsData()
    {
        if (!$eventsData = $this->cache->get(Cache::ETC_CACHE_KEY, self::EVENTS_CACHE_TAG)) {
            $eventsData = $this->eventXmlParser->getEventsData();

            $this->cache->save(Cache::ETC_CACHE_KEY, self::EVENTS_CACHE_TAG, $eventsData);
        }

        return $eventsData;
    }
}
