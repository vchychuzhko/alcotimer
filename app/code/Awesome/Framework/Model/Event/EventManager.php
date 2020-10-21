<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Event;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\Event;
use Awesome\Framework\Model\Event\ObserverInterface;
use Awesome\Framework\Model\Invoker;
use Awesome\Framework\Model\XmlParser\EventXmlParser;

class EventManager
{
    private const EVENTS_CACHE_TAG_GLOBAL = 'events';
    private const EVENTS_CACHE_TAG_PREFIX = 'events_';

    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var EventXmlParser $eventXmlParser
     */
    private $eventXmlParser;

    /**
     * @var Invoker $invoker
     */
    private $invoker;

    /**
     * EventManager constructor.
     * @param Cache $cache
     * @param EventXmlParser $eventXmlParser
     * @param Invoker $invoker
     */
    public function __construct(
        Cache $cache,
        EventXmlParser $eventXmlParser,
        Invoker $invoker
    ) {
        $this->cache = $cache;
        $this->eventXmlParser = $eventXmlParser;
        $this->invoker = $invoker;
    }

    /**
     * Fire event with calling all related observers for a view if specified.
     * @param string $eventName
     * @param array $data
     * @param string $view
     * @return void
     * @throws \Exception
     */
    public function dispatch(string $eventName, array $data = [], string $view = ''): void
    {
        if ($observers = $this->getObservers($eventName, $view)) {
            $event = new Event($eventName, $data);

            foreach ($observers as $observer) {
                /** @var ObserverInterface $observer */
                $observer = $this->invoker->get($observer['class']);

                if (!($observer instanceof ObserverInterface)) {
                    throw new \LogicException(
                        sprintf('Observer "%s" does not implement ObserverInterface', get_class($observer))
                    );
                }
                $observer->execute($event);
            }
        }
    }

    /**
     * Get observers for provided event name for a view if specified.
     * @param string $eventName
     * @param string $view
     * @return array
     */
    public function getObservers(string $eventName, string $view = ''): array
    {
        $eventsData = $this->getEventsData($view);

        return $eventsData[$eventName] ?? [];
    }

    /**
     * Get all declared events.
     * @return array
     */
    public function getEvents(): array
    {
        return array_keys($this->getEventsData());
    }

    /**
     * Get events data for a view if specified.
     * @param string $view
     * @return array
     */
    private function getEventsData(string $view = ''): array
    {
        $tag = $view ? self::EVENTS_CACHE_TAG_PREFIX . $view : self::EVENTS_CACHE_TAG_GLOBAL;

        if (!$eventsData = $this->cache->get(Cache::ETC_CACHE_KEY, $tag)) {
            $eventsData = $this->eventXmlParser->getEventsData($view);

            $this->cache->save(Cache::ETC_CACHE_KEY, $tag, $eventsData);
        }

        return $eventsData;
    }
}
