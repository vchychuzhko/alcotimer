<?php
declare(strict_types=1);

namespace Vch\Framework\Model\XmlParser;

use Vch\Framework\Exception\XmlValidationException;

class RoutesXmlParser extends \Vch\Framework\Model\AbstractXmlParser
{
    private const ROUTES_XML_PATH_PATTERN = '/*/*/etc/%s/routes.xml';
    private const ROUTES_XSD_SCHEMA_PATH = '/Vch/Framework/Schema/routes.xsd';

    /**
     * Get declared routes with their responsive modules.
     * @param string $view
     * @return array
     */
    public function getRoutesData(string $view): array
    {
        $routesData = [];
        $routesXmlFilesPattern = APP_DIR . sprintf(self::ROUTES_XML_PATH_PATTERN, $view);

        foreach (glob($routesXmlFilesPattern) as $routesXmlFile) {
            $parsedData = $this->parse($routesXmlFile);

            foreach ($parsedData as $path => $handles) {
                foreach ($handles as $name => $handle) {
                    if (isset($routesData[$path][$name])) {
                        throw new XmlValidationException(__('Route "%1" is already defined', $name));
                    }

                    $routesData[$path][$name] = $handle;
                }
            }
        }

        return $routesData;
    }

    /**
     * Parse routes XML file.
     * @param string $routesXmlFile
     * @return array
     */
    private function parse(string $routesXmlFile): array
    {
        $parsedNode = [];
        $routesNode = $this->xmlFileManager->parseXmlFileNext($routesXmlFile, APP_DIR . self::ROUTES_XSD_SCHEMA_PATH); // @TODO: Next

        foreach ($routesNode['_route'] as $route) {
            if (!$this->isDisabled($route)) {
                $parsedNode[$route['path']][$route['name']] = $route['handler'];
            }
        }

        return $parsedNode;
    }
}
