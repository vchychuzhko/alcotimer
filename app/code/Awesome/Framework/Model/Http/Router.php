<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Http;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\Action\HttpDefaultAction;
use Awesome\Framework\Model\Action\MaintenanceAction;
use Awesome\Framework\Model\ActionInterface;
use Awesome\Framework\Model\Http\ActionFactory;
use Awesome\Framework\Model\XmlParser\RoutesXmlParser;

class Router
{
    private const ROUTES_CACHE_TAG_PREFIX = 'routes_';

    public const INTERNAL_TYPE = 'internal';
    public const STANDARD_TYPE = 'standard';

    /**
     * @var ActionFactory $actionFactory
     */
    private $actionFactory;

    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var RoutesXmlParser $routesXmlParser
     */
    private $routesXmlParser;

    /**
     * @var array $actions
     */
    private $actions = [];

    /**
     * @var ActionInterface $action
     */
    private $action;

    /**
     * Router constructor.
     * @param ActionFactory $actionFactory
     * @param Cache $cache
     * @param RoutesXmlParser $routesXmlParser
     */
    public function __construct(
        ActionFactory $actionFactory,
        Cache $cache,
        RoutesXmlParser $routesXmlParser
    ) {
        $this->actionFactory = $actionFactory;
        $this->cache = $cache;
        $this->routesXmlParser = $routesXmlParser;
    }

    /**
     * Add action classname and additional parameters to list.
     * @param string $id
     * @param array $data
     * @return $this
     * @throws \LogicException
     */
    public function addAction(string $id, array $data = []): self
    {
        if (!is_a($id, ActionInterface::class, true)) {
            throw new \LogicException(sprintf('Provided action "%s" does not implement ActionInterface', $id));
        }

        $this->actions[] = [
            'id' => $id,
            'data' => $data,
        ];

        return $this;
    }

    /**
     * Get action with the highest priority or default if none found.
     * @return ActionInterface
     * @throws \Exception
     */
    public function getAction(): ActionInterface
    {
        if ($this->action === null) {
            if ($action = reset($this->actions)) {
                $this->action = $this->actionFactory->create($action['id'], $action['data']);
            } else {
                $this->action = $this->actionFactory->create(HttpDefaultAction::class);
            }
        }

        return $this->action;
    }

    /**
     * Get maintenance action.
     * @return ActionInterface
     * @throws \Exception
     */
    public function getMaintenanceAction(): ActionInterface
    {
        return $this->actionFactory->create(MaintenanceAction::class);
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
