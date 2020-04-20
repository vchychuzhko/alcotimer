<?php

namespace Awesome\Framework\Model\XmlParser;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Helper\DataHelper;

class Event extends \Awesome\Framework\Model\AbstractXmlParser
{
    private const EVENTS_XML_PATH_PATTERN = '/*/*/etc/events.xml';
    private const EVENTS_CACHE_TAG = 'events';

    /**
     * @inheritDoc
     * @throws \LogicException
     */
    public function get($handle)
    {
        $observers = $this->getHandlesData();

        return $observers[$handle] ?? null;
    }

    /**
     * @inheritDoc
     * @throws \LogicException
     */
    public function getHandles()
    {
        return array_keys($this->getHandlesData());
    }

    /**
     * Get available handles with their responsible classes.
     * If includeDisabled is true, return also for disabled commands.
     * @return array
     * @throws \LogicException
     */
    public function getHandlesData()
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
            $this->applySortOrder($observers);

            $this->cache->save(Cache::ETC_CACHE_KEY, self::EVENTS_CACHE_TAG, $observers);
        }

        return $observers;
    }

    /**
     * @inheritDoc
     * @throws \LogicException
     */
    protected function parse($node)
    {
        $parsedNode = [];

        foreach ($node->children() as $event) {
            if (!$eventName = $this->getNodeAttribute($event)) {
                throw new \LogicException(sprintf('Name attribute is not provided for event.'));
            }
            $parsedNode[$eventName] = $parsedNode[$eventName] ?? [];

            foreach ($event->children() as $observer) {
                if (!$observerName = $this->getNodeAttribute($observer)) {
                    throw new \LogicException(sprintf('Name attribute is not provided for "%s" event observer.', $eventName));
                }

                if (DataHelper::arrayGetByKeyRecursive($parsedNode, $observerName)) {
                    throw new \LogicException(sprintf('Observer "%s" is defined twice in one file.', $observerName));
                }
                $class = ltrim($this->getNodeAttribute($observer, 'class'), '\\');

                if (!$class) {
                    throw new \LogicException(sprintf('Class is not specified for "%s" observer.', $observerName));
                }
                $disabled = $this->stringBooleanCheck($this->getNodeAttribute($observer, 'disabled'));

                $parsedNode[$eventName][$observerName] = [
                    'class' => '\\' . $class,
                    'disabled' => $disabled
                ];

                if ($sortOrder = $this->getNodeAttribute($observer, 'sortOrder')) {
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
