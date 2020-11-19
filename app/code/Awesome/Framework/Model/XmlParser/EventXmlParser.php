<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\XmlParser;

use Awesome\Framework\Exception\XmlValidationException;
use Awesome\Framework\Helper\DataHelper;
use Awesome\Framework\Helper\XmlParsingHelper;

class EventXmlParser
{
    private const EVENTS_GLOBAL_XML_PATH_PATTERN = '/*/*/etc/events.xml';
    private const EVENTS_XML_PATH_PATTERN = '/*/*/etc/{%s/,}events.xml';

    /**
     * Get available events with their responsible observers for a view if specified.
     * @param string $view
     * @return array
     * @throws XmlValidationException
     */
    public function getEventsData(string $view = ''): array
    {
        $eventsData = [];
        $pattern = $view ? sprintf(self::EVENTS_XML_PATH_PATTERN, $view) : self::EVENTS_GLOBAL_XML_PATH_PATTERN;

        foreach (glob(APP_DIR . $pattern, GLOB_BRACE) as $eventsXmlFile) {
            $parsedData = $this->parse($eventsXmlFile);

            foreach ($parsedData as $eventName => $eventObservers) {
                $eventsData[$eventName] = $eventsData[$eventName] ?? [];

                foreach ($eventObservers as $observerName => $observer) {
                    if (!$observer['disabled']) {
                        if (DataHelper::arrayGetByKeyRecursive($eventsData, $observerName)) {
                            throw new XmlValidationException(sprintf('Observer with "%s" name is already defined', $observerName));
                        }

                        $eventsData[$eventName][$observerName] = $observer;
                    }
                }
            }
        }
        XmlParsingHelper::applySortOrder($eventsData);

        return $eventsData;
    }

    /**
     * Parse events XML file.
     * @param string $eventsXmlFile
     * @return array
     * @throws XmlValidationException
     */
    private function parse(string $eventsXmlFile): array
    {
        $parsedNode = [];
        $eventNode = simplexml_load_file($eventsXmlFile);

        foreach ($eventNode->children() as $event) {
            if (!$eventName = XmlParsingHelper::getNodeAttributeName($event)) {
                throw new XmlValidationException(sprintf('Name attribute is not provided for event in "%s" file', $eventsXmlFile));
            }
            $parsedNode[$eventName] = $parsedNode[$eventName] ?? [];

            foreach ($event->children() as $observer) {
                if (!$observerName = XmlParsingHelper::getNodeAttributeName($observer)) {
                    throw new XmlValidationException(sprintf('Name attribute is not provided for "%s" event observer', $eventName));
                }

                if (DataHelper::arrayGetByKeyRecursive($parsedNode, $observerName)) {
                    throw new XmlValidationException(sprintf('Observer "%s" is defined twice in one file', $observerName));
                }
                $class = ltrim(XmlParsingHelper::getNodeAttribute($observer, 'class'), '\\');

                if (!$class) {
                    throw new XmlValidationException(sprintf('Class is not specified for "%s" observer', $observerName));
                }
                $parsedNode[$eventName][$observerName] = [
                    'class' => '\\' . $class,
                    'disabled' => XmlParsingHelper::isDisabled($observer),
                ];

                if ($sortOrder = XmlParsingHelper::getNodeAttribute($observer, 'sortOrder')) {
                    if (!is_numeric($sortOrder)) {
                        throw new XmlValidationException(sprintf('sortOrder "%s" is not valid', $sortOrder));
                    }
                    $parsedNode[$eventName][$observerName]['sortOrder'] = $sortOrder;
                }
            }
        }

        return $parsedNode;
    }
}
