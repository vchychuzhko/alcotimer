<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Action\HttpErrorAction;
use Awesome\Framework\Model\Action\MaintenanceAction;
use Awesome\Framework\Model\AppState;
use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Event\EventManager;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\ActionResolver;
use Awesome\Framework\Model\Locale;
use Awesome\Framework\Model\Logger;
use Awesome\Framework\Model\Maintenance;

class Http
{
    public const VERSION = '0.5.1';

    public const FRONTEND_VIEW = 'frontend';
    public const BACKEND_VIEW = 'adminhtml';
    public const BASE_VIEW = 'base';

    public const BACKEND_ENABLED_CONFIG = 'backend/enabled';
    public const BACKEND_FRONTNAME_CONFIG = 'backend/front_name';

    /**
     * @var ActionResolver $actionResolver
     */
    protected $actionResolver;

    /**
     * @var AppState $appState
     */
    protected $appState;

    /**
     * @var Config $config
     */
    protected $config;

    /**
     * @var EventManager $eventManager
     */
    protected $eventManager;

    /**
     * @var Locale $locale
     */
    protected $locale;

    /**
     * @var Logger $logger
     */
    protected $logger;

    /**
     * @var Maintenance $maintenance
     */
    protected $maintenance;

    /**
     * @var Request $request
     */
    protected $request;

    /**
     * Http app constructor.
     * @param ActionResolver $actionResolver
     * @param AppState $appState
     * @param Config $config
     * @param EventManager $eventManager
     * @param Locale $locale
     * @param Logger $logger
     * @param Maintenance $maintenance
     */
    public function __construct(
        ActionResolver $actionResolver,
        AppState $appState,
        Config $config,
        EventManager $eventManager,
        Locale $locale,
        Logger $logger,
        Maintenance $maintenance
    ) {
        $this->actionResolver = $actionResolver;
        $this->appState = $appState;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->locale = $locale;
        $this->logger = $logger;
        $this->maintenance = $maintenance;
    }

    /**
     * Run the web application.
     * @return void
     */
    public function run(): void
    {
        try {
            $request = $this->getRequest();

            $this->locale->init($request);

            if (!$this->isMaintenance()) {
                $this->eventManager->dispatch(
                    'http_frontend_action',
                    ['request' => $request, 'action_resolver' => $this->actionResolver],
                    $request->getView()
                );

                $action = $this->actionResolver->getAction();

                $response = $action->execute($request);
            } else {
                /** @var MaintenanceAction $maintenanceAction */
                $maintenanceAction = $this->actionResolver->getMaintenanceAction();

                $response = $maintenanceAction->execute($request);
            }
        } catch (\Exception $e) {
            $errorMessage = get_class_name($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString();

            $this->logger->error($errorMessage);

            $errorAction = new HttpErrorAction(
                $errorMessage,
                $this->appState->isDeveloperMode(),
                isset($request) ? $request->getAcceptType() : null
            );

            $response = $errorAction->execute();
        }

        $response->proceed();
    }

    /**
     * Check if maintenance mode is active for user IP address.
     * @return bool
     */
    protected function isMaintenance(): bool
    {
        $ip = $this->getRequest()->getUserIp();

        return $this->maintenance->isMaintenance($ip);
    }

    /**
     * Parse and return http request.
     * @return Request
     */
    protected function getRequest(): Request
    {
        if (!$this->request) {
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === 443
                ? Request::SCHEME_HTTPS
                : Request::SCHEME_HTTP;
            $url = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $method = $_SERVER['REQUEST_METHOD'];
            $parameters = array_merge($_GET, $_POST);
            $cookies = $_COOKIE;
            $redirectStatus = isset($_SERVER['REDIRECT_STATUS']) ? (int) $_SERVER['REDIRECT_STATUS'] : null;
            $acceptType = isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] !== '*/*'
                ? strtok($_SERVER['HTTP_ACCEPT'], ',')
                : null;
            $userIp = $_SERVER['REMOTE_ADDR'];
            $view = self::FRONTEND_VIEW;
            $path = trim(parse_url($url, PHP_URL_PATH), '/');

            if ($this->config->get(self::BACKEND_ENABLED_CONFIG)) {
                $backendFrontName = $this->config->get(self::BACKEND_FRONTNAME_CONFIG);

                $view = preg_match('/^' . $backendFrontName . '\//', $path) ? self::BACKEND_VIEW : $view;
                $path = preg_replace('/^' . $backendFrontName . '\//', '', $path);
            }
            @list($route, $entity, $action) = explode('/', $path);

            $this->request = new Request($url, $method, $parameters, $cookies, $redirectStatus, [
                'accept_type' => $acceptType,
                'route'       => $route ?: 'index',
                'entity'      => $entity,
                'action'      => $action,
                'user_ip'     => $userIp,
                'view'        => $view,
            ]);
        }

        return $this->request;
    }
}
