<?php

namespace Awesome\Framework\Model\Http;

use Awesome\Framework\Model\ActionInterface;
use Awesome\Framework\Model\Invoker;

class Router
{
    /**
     * @var array $actions
     */
    private $actions = [];

    /**
     * @var Invoker $invoker
     */
    private $invoker;

    /**
     * Router constructor.
     * @param Invoker $invoker
     */
    public function __construct(Invoker $invoker)
    {
        $this->invoker = $invoker;
    }

    /**
     * Add action classname to list.
     * @param string $action
     * @return $this
     */
    public function addAction($action)
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * Get action with the highest priority.
     * @return ActionInterface|null
     * @throws \Exception
     */
    public function getAction()
    {
        if ($action = reset($this->actions)) {
            $action = $this->invoker->get($action);

            if (!($action instanceof ActionInterface)) {
                throw new \LogicException(sprintf('Action "%s" does not implement ActionInterface', get_class($action)));
            }
        }

        return $action ?: null;
    }
}
