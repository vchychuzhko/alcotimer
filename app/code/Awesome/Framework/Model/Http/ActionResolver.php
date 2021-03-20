<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Http;

use Awesome\Framework\Model\Action\HttpDefaultAction;
use Awesome\Framework\Model\Action\MaintenanceAction;
use Awesome\Framework\Model\ActionInterface;
use Awesome\Framework\Model\Http\ActionFactory;

class ActionResolver
{
    /**
     * @var ActionFactory $actionFactory
     */
    private $actionFactory;

    /**
     * @var array $actions
     */
    private $actions = [];

    /**
     * @var ActionInterface $action
     */
    private $action;

    /**
     * ActionResolver constructor.
     * @param ActionFactory $actionFactory
     */
    public function __construct(ActionFactory $actionFactory)
    {
        $this->actionFactory = $actionFactory;
    }

    /**
     * Add action classname to actions list.
     * @param string $actionId
     * @return $this
     * @throws \LogicException
     */
    public function addAction(string $actionId): self
    {
        if (!is_a($actionId, ActionInterface::class, true)) {
            throw new \LogicException(sprintf('Provided action "%s" does not implement ActionInterface', $actionId));
        }

        $this->actions[] = $actionId;

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
                $this->action = $this->actionFactory->create($action);
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
}
