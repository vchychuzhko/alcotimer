<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Http;

use Awesome\Framework\Model\ActionInterface;

class ActionFactory extends \Awesome\Framework\Model\AbstractFactory
{
    /**
     * Create action object with provided data.
     * @param string $actionId
     * @param array $data
     * @return ActionInterface
     * @throws \Exception
     */
    public function create(string $actionId, array $data = []): ActionInterface
    {
        return $this->invoker->create($actionId, ['data' => $data]);
    }
}
