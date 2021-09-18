<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\XmlParser;

use Awesome\Framework\Exception\XmlValidationException;
use Awesome\Framework\Helper\DataHelper;
use Awesome\Framework\Helper\XmlParsingHelper;
use Awesome\Framework\Model\FileManager\XmlFileManager;

class EventXmlParser
{
    private const EVENTS_GLOBAL_XML_PATH_PATTERN = '/*/*/etc/events.xml';
    private const EVENTS_XML_PATH_PATTERN = '/*/*/etc/{%s/,}events.xml';
    private const EVENTS_XSD_SCHEMA_PATH = '/Awesome/Framework/Schema/events.xsd';

    /**
     * @var XmlFileManager $xmlFileManager
     */
    private $xmlFileManager;

    /**
     * EventXmlParser constructor.
     * @param XmlFileManager $xmlFileManager
     */
    public function __construct(XmlFileManager $xmlFileManager)
    {
        $this->xmlFileManager = $xmlFileManager;
    }

    /**
     * Get available events with their responsible observers for a view if specified.
     * @param string $view
     * @return array
     * @throws \Exception
     */
    public function getEventsData(string $view = ''): array
    {
        $eventsData = [];
        $eventsXmlFilesPattern = APP_DIR . ($view ? sprintf(self::EVENTS_XML_PATH_PATTERN, $view) : self::EVENTS_GLOBAL_XML_PATH_PATTERN);

        foreach (glob($eventsXmlFilesPattern, GLOB_BRACE) as $eventsXmlFile) {
            $parsedData = $this->parse($eventsXmlFile);

            foreach ($parsedData as $eventName => $eventObservers) {
                $eventsData[$eventName] = $eventsData[$eventName] ?? [];

                foreach ($eventObservers as $observerName => $observer) {
                    if (DataHelper::arrayGetByKeyRecursive($eventsData, $observerName)) {
                        throw new XmlValidationException(__('Observer "%1" is already defined', $observerName));
                    }

                    $eventsData[$eventName][$observerName] = $observer;
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
     * @throws \Exception
     */
    private function parse(string $eventsXmlFile): array
    {
        $parsedNode = [];
        $eventNode = $this->xmlFileManager->parseXmlFile($eventsXmlFile, APP_DIR . self::EVENTS_XSD_SCHEMA_PATH);

        foreach ($eventNode->children() as $event) {
            $eventName = XmlParsingHelper::getNodeAttributeName($event);
            $parsedNode[$eventName] = $parsedNode[$eventName] ?? [];

            foreach ($event->children() as $observer) {
                if (!XmlParsingHelper::isDisabled($observer)) {
                    $observerName = XmlParsingHelper::getNodeAttributeName($observer);

                    $parsedNode[$eventName][$observerName] = [
                        'class'     => '\\' . ltrim(XmlParsingHelper::getNodeAttribute($observer, 'class'), '\\'),
                        'sortOrder' => XmlParsingHelper::getNodeAttribute($observer, 'sortOrder'),
                    ];
                }
            }
        }

        return $parsedNode;
    }
}
