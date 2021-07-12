<?php
declare(strict_types=1);

namespace Awesome\Controller\Observer;

use Awesome\Controller\Model\PostControllerInterface;
use Awesome\Framework\Helper\DataHelper;
use Awesome\Framework\Model\Event;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Http\ActionResolver;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Router;

class ControllerRoutingObserver implements \Awesome\Framework\Model\Event\ObserverInterface
{
    private const ADMINHTML_CONTROLLER_FOLDER = 'Adminhtml';
    private const CONTROLLER_FOLDER = 'Controller';

    private const DEFAULT_CONTROLLER_NAME = 'Index';

    /**
     * @var Router $router
     */
    private $router;

    /**
     * ControllerRoutingObserver constructor.
     * @param Router $router
     */
    public function __construct(Router $router)
    {
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
        $route = $request->getRoute();
        $view = $request->getView();

        if ($route && $module = $this->router->getStandardRoute($route, $view)) {
            $className = str_replace('_', '\\', $module) . '\\' . self::CONTROLLER_FOLDER
                . ($view === Http::BACKEND_VIEW ? '\\' . self::ADMINHTML_CONTROLLER_FOLDER : '');

            if ($entity = $request->getEntity()) {
                $className .= '\\' . DataHelper::PascalCase($entity);
            } else {
                $className .= '\\' . self::DEFAULT_CONTROLLER_NAME;
            }

            if ($action = $request->getAction()) {
                $className .= '\\' . DataHelper::PascalCase($action);
            } elseif (!class_exists($className)) {
                $className .= '\\' . self::DEFAULT_CONTROLLER_NAME;
            }

            if (class_exists($className)
                && is_a($className, PostControllerInterface::class, true) === $request->isPost()
            ) {
                $actionResolver->addAction($className);
            }
        }
    }
}
