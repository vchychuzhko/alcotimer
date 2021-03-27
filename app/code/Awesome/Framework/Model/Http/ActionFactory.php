<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Http;

use Awesome\Framework\Model\ActionInterface;

class ActionFactory extends \Awesome\Framework\Model\AbstractFactory
{
    /**
     * Create action object.
     * @param string $actionId
     * @return ActionInterface
     */
    public function create(string $actionId): ActionInterface
    {
        return $this->invoker->create($actionId);
    }
}
