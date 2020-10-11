<?php

namespace Awesome\Framework\Model\Http;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\AbstractAction;
use Awesome\Framework\Model\Invoker;
use Awesome\Framework\Model\XmlParser\RoutesXmlParser;

class Router
{
    private const ROUTES_CACHE_TAG_PREFIX = 'routes_';

    public const INTERNAL_TYPE = 'internal';
    public const STANDARD_TYPE = 'standard';

    /**
     * @var array $actions
     */
    private $actions = [];

    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var Invoker $invoker
     */
    private $invoker;

    /**
     * @var RoutesXmlParser $routesXmlParser
     */
    private $routesXmlParser;

    /**
     * Router constructor.
     * @param Cache $cache
     * @param Invoker $invoker
     * @param RoutesXmlParser $routesXmlParser
     */
    public function __construct(
        Cache $cache,
        Invoker $invoker,
        RoutesXmlParser $routesXmlParser
    ) {
        $this->cache = $cache;
        $this->invoker = $invoker;
        $this->routesXmlParser = $routesXmlParser;
    }

    /**
     * Add action classname and additional parameters to list.
     * @param string $id
     * @param array $data
     * @return $this
     */
    public function addAction($id, $data = [])
    {
        $this->actions[] = [
            'id' => $id,
            'data' => $data,
        ];

        return $this;
    }

    /**
     * Get action with the highest priority.
     * @return AbstractAction|null
     * @throws \Exception
     */
    public function getAction()
    {
        if ($action = reset($this->actions)) {
            $action = $this->invoker->make($action['id'], ['data' => $action['data']]);

            if (!($action instanceof AbstractAction)) {
                throw new \LogicException(sprintf('Action "%s" does not extends AbstractAction class', get_class($action)));
            }
        }

        return $action ?: null;
    }

    /**
     * Check if provided route is a registered standard route and return its responsible module name.
     * @param string $route
     * @param string $view
     * @return string|null
     */
    public function getStandardRoute($route, $view)
    {
        $routes = $this->getStandardRoutes($view);

        return $routes[$route] ?? null;
    }

    /**
     * Get standard (public) routes for a specified view.
     * @param string $view
     * @return array
     */
    public function getStandardRoutes($view)
    {
        $routes = $this->getRoutes($view);

        return $routes[self::STANDARD_TYPE] ?? [];
    }

    /**
     * Get internal (system) routes for a specified view.
     * @param string $view
     * @return array
     */
    public function getInternalRoutes($view)
    {
        $routes = $this->getRoutes($view);

        return $routes[self::INTERNAL_TYPE] ?? [];
    }

    /**
     * Get all registered routes for a specified view.
     * @param string $view
     * @return array
     */
    private function getRoutes($view)
    {
        if (!$routes = $this->cache->get(Cache::ETC_CACHE_KEY, self::ROUTES_CACHE_TAG_PREFIX . $view)) {
            $routes = $this->routesXmlParser->getRoutesData($view);

            $this->cache->save(Cache::ETC_CACHE_KEY, self::ROUTES_CACHE_TAG_PREFIX . $view, $routes);
        }

        return $routes;
    }
}
