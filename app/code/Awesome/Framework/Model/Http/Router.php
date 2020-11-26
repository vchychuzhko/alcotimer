<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Http;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\XmlParser\RoutesXmlParser;

class Router
{
    private const ROUTES_CACHE_TAG_PREFIX = 'routes_';

    public const INTERNAL_TYPE = 'internal';
    public const STANDARD_TYPE = 'standard';

    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var RoutesXmlParser $routesXmlParser
     */
    private $routesXmlParser;

    /**
     * Router constructor.
     * @param Cache $cache
     * @param RoutesXmlParser $routesXmlParser
     */
    public function __construct(
        Cache $cache,
        RoutesXmlParser $routesXmlParser
    ) {
        $this->cache = $cache;
        $this->routesXmlParser = $routesXmlParser;
    }

    /**
     * Check if provided route is a registered standard route and return its responsible module name.
     * @param string $route
     * @param string $view
     * @return string|null
     */
    public function getStandardRoute(string $route, string $view): ?string
    {
        $routes = $this->getStandardRoutes($view);

        return $routes[$route] ?? null;
    }

    /**
     * Get standard (public) routes for a specified view.
     * @param string $view
     * @return array
     */
    public function getStandardRoutes(string $view): array
    {
        $routes = $this->getRoutes($view);

        return $routes[self::STANDARD_TYPE] ?? [];
    }

    /**
     * Get internal (system) routes for a specified view.
     * @param string $view
     * @return array
     */
    public function getInternalRoutes(string $view): array
    {
        $routes = $this->getRoutes($view);

        return $routes[self::INTERNAL_TYPE] ?? [];
    }

    /**
     * Get all registered routes for a specified view.
     * @param string $view
     * @return array
     */
    private function getRoutes(string $view): array
    {
        return $this->cache->get(Cache::ETC_CACHE_KEY, self::ROUTES_CACHE_TAG_PREFIX . $view, function () use ($view) {
            return $this->routesXmlParser->getRoutesData($view);
        });
    }
}
