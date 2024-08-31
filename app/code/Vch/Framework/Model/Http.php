<?php
declare(strict_types=1);

namespace Vch\Framework\Model;

use Vch\Framework\Exception\NotFoundException;
use Vch\Framework\Exception\UnauthorizedException;
use Vch\Framework\Model\Action\HttpErrorAction;
use Vch\Framework\Model\Action\MaintenanceAction;
use Vch\Framework\Model\Action\NotFoundAction;
use Vch\Framework\Model\Action\UnauthorizedAction;
use Vch\Framework\Model\AppState;
use Vch\Framework\Model\Http\Request;
use Vch\Framework\Model\Http\Router;
use Vch\Framework\Model\Logger;
use Vch\Framework\Model\Maintenance;

class Http
{
    public const VERSION = '0.6.2';

    public const FRONTEND_VIEW = 'frontend';
    public const BACKEND_VIEW = 'adminhtml';
    public const BASE_VIEW = 'base';

    private AppState $appState;

    private Logger $logger;

    private Maintenance $maintenance;

    private Router $router;

    /**
     * Http app constructor.
     * @param AppState $appState
     * @param Logger $logger
     * @param Maintenance $maintenance
     * @param Router $router
     */
    public function __construct(
        AppState $appState,
        Logger $logger,
        Maintenance $maintenance,
        Router $router
    ) {
        $this->appState = $appState;
        $this->logger = $logger;
        $this->maintenance = $maintenance;
        $this->router = $router;
    }

    /**
     * Run the web application.
     */
    public function run()
    {
        try {
            $request = Request::getInstance();

            if (!$this->maintenance->isActive($request->getUserIp())) {
                $action = $this->router->match($request);

                $response = $action->execute($request);
            } else {
                /** @var MaintenanceAction $maintenanceAction */
                $maintenanceAction = $this->router->getMaintenanceAction();

                $response = $maintenanceAction->execute($request);
            }
        } catch (NotFoundException $e) {
            /** @var NotFoundAction $unauthorizedAction */
            $notFoundAction = $this->router->getNotFoundAction();

            $response = $notFoundAction->execute($request);
        } catch (UnauthorizedException $e) {
            /** @var UnauthorizedAction $unauthorizedAction */
            $unauthorizedAction = $this->router->getUnauthorizedAction();

            $this->logger->info($e->getMessage(), Logger::INFO_WARNING_LEVEL);

            $response = $unauthorizedAction->execute($request);
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
