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
     * @var string $view
     */
    private $view;

    /**
     * Router constructor.
     * @param string $view
     */
    public function __construct($view)
    {
        $this->view = $view;
    }

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

    /**
     * Get requested view.
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }
}
