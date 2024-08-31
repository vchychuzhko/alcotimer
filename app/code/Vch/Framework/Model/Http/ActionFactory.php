<?php
declare(strict_types=1);

namespace Vch\Framework\Model\Http;

use Vch\Framework\Model\ActionInterface;

class ActionFactory extends \Vch\Framework\Model\AbstractFactory
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
