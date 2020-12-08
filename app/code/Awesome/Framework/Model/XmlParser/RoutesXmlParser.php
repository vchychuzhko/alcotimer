<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\XmlParser;

use Awesome\Framework\Exception\XmlValidationException;
use Awesome\Framework\Helper\XmlParsingHelper;
use Awesome\Framework\Model\FileManager\XmlFileManager;
use Awesome\Framework\Model\Http\Router;

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

                foreach ($routes as $routeFrontName => $routeData) {
                    if (isset($routesData[$routeType][$routeFrontName])) {
                        throw new XmlValidationException(sprintf('Route with "%s" front name is already defined', $routeFrontName));
                    }

                    $routesData[$routeType][$routeFrontName] = $routeData;
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
        $parsedNode = [
            Router::INTERNAL_TYPE => [],
            Router::STANDARD_TYPE => [],
        ];
        $routesNode = $this->xmlFileManager->parseXmlFile($routesXmlFile, APP_DIR . self::ROUTES_XSD_SCHEMA_PATH);

        foreach ($routesNode->children() as $route) {
            if (!XmlParsingHelper::isDisabled($route)) {
                $routeType = XmlParsingHelper::getNodeAttribute($route, 'type');

                if (!isset($parsedNode[$routeType])) {
                    throw new XmlValidationException(sprintf('Route type "%s" is not recognized', $routeType));
                }
                $routeFrontName = XmlParsingHelper::getNodeAttribute($route, 'frontName');

                if (isset($parsedNode[$routeType][$routeFrontName])) {
                    throw new XmlValidationException(sprintf('Route "%s" is defined twice in one file', $routeFrontName));
                }
                $module = XmlParsingHelper::getChildNode($route, 'module');
                $moduleName = XmlParsingHelper::getNodeAttributeName($module);

                $parsedNode[$routeType][$routeFrontName] = $moduleName;
            }
        }

        return $parsedNode;
    }
}
