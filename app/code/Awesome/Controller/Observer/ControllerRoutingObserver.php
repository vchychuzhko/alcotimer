<?php
declare(strict_types=1);

namespace Awesome\Controller\Observer;

use Awesome\Framework\Helper\DataHelper;
use Awesome\Framework\Model\Event;
use Awesome\Framework\Model\FileManager\PhpFileManager;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Http\ActionResolver;
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
     * @var Router $router
     */
    private $router;

    /**
     * ControllerRoutingObserver constructor.
     * @param PhpFileManager $phpFileManager
     * @param Router $router
     */
    public function __construct(PhpFileManager $phpFileManager, Router $router)
    {
        $this->phpFileManager = $phpFileManager;
        $this->router = $router;
    }

    /**
     * Resolve controller routing.
     * @inheritDoc
     */
    public function execute(Event $event): void
    {
        /** @var ActionResolver $actionResolver */
        $actionResolver = $event->getActionResolver();
        /** @var Request $request */
        $request = $event->getRequest();
        $view = $request->getView();

        if ($module = $this->router->getStandardRoute($request->getRoute(), $view)) {
            $module = str_replace('_', '\\', $module);
            $controllerFolder = $view === Http::BACKEND_VIEW
                ? self::ADMINHTML_CONTROLLER_FOLDER
                : self::DEFAULT_CONTROLLER_FOLDER;
            $className = $module . '\\' . $controllerFolder;

            if ($entity = $request->getEntity()) {
                $className .= '\\' . ucfirst(DataHelper::camelCase($entity));
            } else {
                $className .= '\\' . self::DEFAULT_CONTROLLER_NAME;
            }

            if ($action = $request->getAction()) {
                $className .= '\\' . ucfirst(DataHelper::camelCase($action));
            } elseif (!$this->phpFileManager->objectFileExists($className)) {
                $className .= '\\' . self::DEFAULT_CONTROLLER_NAME;
            }

            if ($this->phpFileManager->objectFileExists($className)) {
                $actionResolver->addAction($className);
            }
        }
    }
}
