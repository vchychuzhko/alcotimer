<?php

namespace Awesome\Framework\Model\Http;

use Awesome\Framework\Model\ActionInterface;

class Router
{
    /**
     * @var array $actions
     */
    private $actions = [];

    /**
     * Add action to list.
     * @param ActionInterface $action
     * @return $this
     */
    public function addAction($action)
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * Get action with the highest priority.
     * @return ActionInterface
     */
    public function getAction()
    {
        return reset($this->actions);
    }
}
