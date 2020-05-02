<?php

namespace Awesome\Framework\Model\XmlParser;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Helper\DataHelper;
use Awesome\Framework\Helper\XmlParsingHelper;

class Event
{
    private const EVENTS_XML_PATH_PATTERN = '/*/*/etc/events.xml';
    private const EVENTS_CACHE_TAG = 'events';

    /**
     * @var Cache $cache
     */
    protected $cache;

    /**
     * Event constructor.
     */
    function __construct()
    {
        $this->cache = new Cache();
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
     * Get available events with their responsible observers.
     * @return array
     * @throws \LogicException
     */
    public function getEventsData()
    {
        if (!$observers = $this->cache->get(Cache::ETC_CACHE_KEY, self::EVENTS_CACHE_TAG)) {
            $observers = [];

            foreach (glob(APP_DIR . self::EVENTS_XML_PATH_PATTERN) as $eventsXmlFile) {
                $eventsData = simplexml_load_file($eventsXmlFile);
                $parsedData = $this->parse($eventsData);

                foreach ($parsedData as $eventName => $eventObservers) {
                    if ($eventObservers) {
                        $observers[$eventName] = $observers[$eventName] ?? [];

                        foreach ($eventObservers as $observerName => $observer) {
                            if (DataHelper::arrayGetByKeyRecursive($observers, $observerName)) {
                                throw new \LogicException(sprintf('Observer with "%s" name is already defined', $eventName));
                            }

                            if (!$observer['disabled']) {
                                $observers[$eventName][$observerName] = $observer['class'];
                            }
                        }
                    }
                }
            }
            XmlParsingHelper::applySortOrder($observers);

            $this->cache->save(Cache::ETC_CACHE_KEY, self::EVENTS_CACHE_TAG, $observers);
        }

        return $observers;
    }

    /**
     * Parse event node.
     * @param \SimpleXMLElement $eventNode
     * @return array
     * @throws \LogicException
     */
    private function parse($eventNode)
    {
        $parsedNode = [];

        foreach ($eventNode->children() as $event) {
            if (!$eventName = XmlParsingHelper::getNodeAttribute($event)) {
                throw new \LogicException(sprintf('Name attribute is not provided for event.'));
            }
            $parsedNode[$eventName] = $parsedNode[$eventName] ?? [];

            foreach ($event->children() as $observer) {
                if (!$observerName = XmlParsingHelper::getNodeAttribute($observer)) {
                    throw new \LogicException(sprintf('Name attribute is not provided for "%s" event observer.', $eventName));
                }

                if (DataHelper::arrayGetByKeyRecursive($parsedNode, $observerName)) {
                    throw new \LogicException(sprintf('Observer "%s" is defined twice in one file.', $observerName));
                }
                $class = ltrim(XmlParsingHelper::getNodeAttribute($observer, 'class'), '\\');

                if (!$class) {
                    throw new \LogicException(sprintf('Class is not specified for "%s" observer.', $observerName));
                }
                $disabled = XmlParsingHelper::stringBooleanCheck(XmlParsingHelper::getNodeAttribute($observer, 'disabled'));

                $parsedNode[$eventName][$observerName] = [
                    'class' => '\\' . $class,
                    'disabled' => $disabled
                ];

                if ($sortOrder = XmlParsingHelper::getNodeAttribute($observer, 'sortOrder')) {
                    if (!is_numeric($sortOrder)) {
                        throw new \LogicException(sprintf('sortOrder "%s" is not valid.', $sortOrder));
                    }
                    $parsedNode[$eventName][$observerName]['sortOrder'] = $sortOrder;
                }
            }
        }

        return $parsedNode;
    }
}
