<?php

namespace Awesome\Controller\Observer;

use Awesome\Framework\Helper\DataHelper;
use Awesome\Framework\Model\FileManager\PhpFileManager;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Router;

class ControllerRoutingObserver implements \Awesome\Framework\Model\Event\ObserverInterface
{
    private const ADMINHTML_CONTROLLER_FOLDER = 'Controller/Adminhtml';
    private const DEFAULT_CONTROLLER_FOLDER = 'Controller';

    private const DEFAULT_CONTROLLER_NAME = 'Index';

    /**
     * @var PhpFileManager $phpFileManager
     */
    private $phpFileManager;

    /**
     * ControllerRoutingObserver constructor.
     * @param PhpFileManager $phpFileManager
     */
    public function __construct(PhpFileManager $phpFileManager)
    {
        $this->phpFileManager = $phpFileManager;
    }

    /**
     * Resolve controller routing.
     * @inheritDoc
     */
    public function execute($event)
    {
        /** @var Router $router */
        $router = $event->getRouter();
        /** @var Request $request */
        $request = $event->getRequest();
        $view = $request->getView();

        $routes = $router->getStandardRoutes($view);
        @list($route, $entity, $action) = explode('/', ltrim($request->getPath(), '/'));

        if (isset($routes[$route])) {
            $module = str_replace('_', '\\', $routes[$route]);
            $controllerFolder = $view === Http::BACKEND_VIEW
                ? self::ADMINHTML_CONTROLLER_FOLDER
                : self::DEFAULT_CONTROLLER_FOLDER;
            $className = $module . '\\' . $controllerFolder;

            if (isset($entity)) {
                $className .= '\\' . ucfirst(DataHelper::camelCase($entity));
            } else {
                $className .= '\\' . self::DEFAULT_CONTROLLER_NAME;
            }

            if (isset($action)) {
                $className .= '\\' . ucfirst(DataHelper::camelCase($action));
            } elseif (!$this->phpFileManager->objectFileExists($className)) {
                $className .= '\\' . self::DEFAULT_CONTROLLER_NAME;
            }

            if ($this->phpFileManager->objectFileExists($className)) {
                $router->addAction($className);
            }
        }
    }
}
