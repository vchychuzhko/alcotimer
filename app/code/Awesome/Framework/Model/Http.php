<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Exception\NotFoundException;
use Awesome\Framework\Exception\UnauthorizedException;
use Awesome\Framework\Model\Action\HttpErrorAction;
use Awesome\Framework\Model\Action\MaintenanceAction;
use Awesome\Framework\Model\Action\NotFoundAction;
use Awesome\Framework\Model\Action\UnauthorizedAction;
use Awesome\Framework\Model\AppState;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Router;
use Awesome\Framework\Model\Logger;
use Awesome\Framework\Model\Maintenance;

class Http
{
    public const VERSION = '0.6.0';

    public const FRONTEND_VIEW = 'frontend';
    public const BACKEND_VIEW = 'adminhtml';
    public const BASE_VIEW = 'base';

    private AppState $appState;

    private Logger $logger;

    private Maintenance $maintenance;

    private Request $request;

    private Router $router;

    /**
     * Http app constructor.
     * @param AppState $appState
     * @param Logger $logger
     * @param Maintenance $maintenance
     * @param Request $request
     * @param Router $router
     */
    public function __construct(
        AppState $appState,
        Logger $logger,
        Maintenance $maintenance,
        Request $request,
        Router $router
    ) {
        $this->appState = $appState;
        $this->logger = $logger;
        $this->maintenance = $maintenance;
        $this->request = $request;
        $this->router = $router;
    }

    /**
     * Run the web application.
     */
    public function run()
    {
        try {
            if (!$this->maintenance->isActive($this->request->getUserIp())) {
                $action = $this->router->match($this->request);

                $response = $action->execute($this->request);
            } else {
                /** @var MaintenanceAction $maintenanceAction */
                $maintenanceAction = $this->router->getMaintenanceAction();

                $response = $maintenanceAction->execute($this->request);
            }
        } catch (NotFoundException $e) {
            /** @var NotFoundAction $unauthorizedAction */
            $notFoundAction = $this->router->getNotFoundAction();

            $response = $notFoundAction->execute($this->request);
        } catch (UnauthorizedException $e) {
            /** @var UnauthorizedAction $unauthorizedAction */
            $unauthorizedAction = $this->router->getUnauthorizedAction();

            $this->logger->info($e->getMessage(), Logger::INFO_WARNING_LEVEL);

            $response = $unauthorizedAction->execute($this->request);
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
     * Get all defined application views.
     * @return array
     */
    public static function getAllViews(): array
    {
        return [
            self::FRONTEND_VIEW,
            self::BACKEND_VIEW,
        ];
    }
}
