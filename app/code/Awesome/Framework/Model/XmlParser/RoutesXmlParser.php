<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\XmlParser;

use Awesome\Framework\Exception\XmlValidationException;
use Awesome\Framework\Helper\XmlParsingHelper;
use Awesome\Framework\Model\FileManager\XmlFileManager;

class RoutesXmlParser
{
    private const ROUTES_XML_PATH_PATTERN = '/*/*/etc/%s/routes.xml';
    private const ROUTES_XSD_SCHEMA_PATH = '/Awesome/Framework/Schema/routes.xsd';

    /**
     * @var XmlFileManager $xmlFileManager
     */
    private $xmlFileManager;

    /**
     * RoutesXmlParser constructor.
     * @param XmlFileManager $xmlFileManager
     */
    public function __construct(XmlFileManager $xmlFileManager)
    {
        $this->xmlFileManager = $xmlFileManager;
    }

    /**
     * Get declared routes with their responsive modules.
     * @param string $view
     * @return array
     * @throws \Exception
     */
    public function getRoutesData(string $view): array
    {
        $routesData = [];
        $routesXmlFilesPattern = APP_DIR . sprintf(self::ROUTES_XML_PATH_PATTERN, $view);

        foreach (glob($routesXmlFilesPattern) as $routesXmlFile) {
            $parsedData = $this->parse($routesXmlFile);

            foreach ($parsedData as $routeType => $routes) {
                $routesData[$routeType] = $routesData[$routeType] ?? [];

                foreach ($routes as $routeName => $routeData) {
                    if (isset($routesData[$routeType][$routeName])) {
                        throw new XmlValidationException(__('Route "%1" is already defined', $routeName));
                    }

                    $routesData[$routeType][$routeName] = $routeData;
                }
            }
        }

        return $routesData;
    }

    /**
     * Parse routes XML file.
     * @param string $routesXmlFile
     * @return array
     * @throws \Exception
     */
    private function parse(string $routesXmlFile): array
    {
        $parsedNode = [];
        $routesNode = $this->xmlFileManager->parseXmlFile($routesXmlFile, APP_DIR . self::ROUTES_XSD_SCHEMA_PATH);

        foreach ($routesNode->children() as $route) {
            if (!XmlParsingHelper::isDisabled($route)) {
                $routeType = XmlParsingHelper::getNodeAttribute($route, 'type');

                $routeName = XmlParsingHelper::getNodeAttributeName($route);

                $parsedNode[$routeType][$routeName] = XmlParsingHelper::getNodeAttributeName(
                    XmlParsingHelper::getChildNode($route, 'module')
                );
            }
        }

        return $parsedNode;
    }
}
