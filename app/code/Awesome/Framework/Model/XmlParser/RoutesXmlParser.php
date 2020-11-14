<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\XmlParser;

use Awesome\Framework\Exception\XmlValidationException;
use Awesome\Framework\Helper\XmlParsingHelper;
use Awesome\Framework\Model\Http\Router;

class RoutesXmlParser
{
    private const ROUTES_XML_PATH_PATTERN = '/*/*/etc/%s/routes.xml';

    /**
     * Get declared routes with their responsive modules.
     * @param string $view
     * @return array
     * @throws XmlValidationException
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
     * @throws XmlValidationException
     */
    private function parse(string $routesXmlFile): array
    {
        $parsedNode = [
            Router::INTERNAL_TYPE => [],
            Router::STANDARD_TYPE => [],
        ];
        $routesNode = simplexml_load_file($routesXmlFile);

        foreach ($routesNode->children() as $route) {
            if (!XmlParsingHelper::isAttributeBooleanTrue($route)) {
                if (!$routeFrontName = XmlParsingHelper::getNodeAttribute($route, 'frontName')) {
                    throw new XmlValidationException(sprintf('FrontName attribute is not provided for route in "%s" file', $routesXmlFile));
                }
                if (!$routeType = XmlParsingHelper::getNodeAttribute($route, 'type')) {
                    throw new XmlValidationException(sprintf('Type attribute is not provided for "%s" route', $routeFrontName));
                }
                if (!isset($parsedNode[$routeType])) {
                    throw new XmlValidationException(sprintf('Route type "%s" is not recognized', $routeType));
                }
                if (isset($parsedNode[$routeType][$routeFrontName])) {
                    throw new XmlValidationException(sprintf('Route "%s" is defined twice in one file', $routeFrontName));
                }
                if (!$module = $route->module) {
                    throw new XmlValidationException(sprintf('Responsible module is not provided for "%s" route', $routeFrontName));
                }
                if (!$moduleName = XmlParsingHelper::getNodeAttribute($module)) {
                    throw new XmlValidationException(sprintf('Module name attribute is not provided for "%s" route', $routeFrontName));
                }
                $parsedNode[$routeType][$routeFrontName] = $moduleName;
            }
        }

        return $parsedNode;
    }
}
