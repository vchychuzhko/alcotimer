<?php

namespace Awesome\Framework\Model\XmlParser;

use Awesome\Framework\Helper\DataHelper;
use Awesome\Framework\Helper\XmlParsingHelper;

class EventXmlParser
{
    private const EVENTS_XML_PATH_PATTERN = '/*/*/etc/events.xml';

    /**
     * Get available events with their responsible observers.
     * @return array
     * @throws \LogicException
     */
    public function getEventsData()
    {
        $eventsData = [];

        foreach (glob(APP_DIR . self::EVENTS_XML_PATH_PATTERN) as $eventsXmlFile) {
            $collectedEventsData = simplexml_load_file($eventsXmlFile);
            $parsedData = $this->parse($collectedEventsData);

            foreach ($parsedData as $eventName => $eventObservers) {
                if ($eventObservers) {
                    $eventsData[$eventName] = $eventsData[$eventName] ?? [];

                    foreach ($eventObservers as $observerName => $observer) {
                        if (DataHelper::arrayGetByKeyRecursive($eventsData, $observerName)) {
                            throw new \LogicException(sprintf('Observer with "%s" name is already defined', $observerName));
                        }

                        if (!$observer['disabled']) {
                            $eventsData[$eventName][$observerName] = $observer['class'];
                        }
                    }
                }
            }
        }
        XmlParsingHelper::applySortOrder($eventsData);

        return $eventsData;
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
                throw new \LogicException(sprintf('Name attribute is not provided for event'));
            }
            $parsedNode[$eventName] = $parsedNode[$eventName] ?? [];

            foreach ($event->children() as $observer) {
                if (!$observerName = XmlParsingHelper::getNodeAttribute($observer)) {
                    throw new \LogicException(sprintf('Name attribute is not provided for "%s" event observer', $eventName));
                }

                if (DataHelper::arrayGetByKeyRecursive($parsedNode, $observerName)) {
                    throw new \LogicException(sprintf('Observer "%s" is defined twice in one file', $observerName));
                }
                $class = ltrim(XmlParsingHelper::getNodeAttribute($observer, 'class'), '\\');

                if (!$class) {
                    throw new \LogicException(sprintf('Class is not specified for "%s" observer', $observerName));
                }
                $disabled = XmlParsingHelper::isAttributeBooleanTrue($observer);

                $parsedNode[$eventName][$observerName] = [
                    'class' => '\\' . $class,
                    'disabled' => $disabled
                ];

                if ($sortOrder = XmlParsingHelper::getNodeAttribute($observer, 'sortOrder')) {
                    if (!is_numeric($sortOrder)) {
                        throw new \LogicException(sprintf('sortOrder "%s" is not valid', $sortOrder));
                    }
                    $parsedNode[$eventName][$observerName]['sortOrder'] = $sortOrder;
                }
            }
        }

        return $parsedNode;
    }
}
