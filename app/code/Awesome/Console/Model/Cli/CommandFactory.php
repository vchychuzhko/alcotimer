<?php
declare(strict_types=1);

namespace Awesome\Console\Model\Cli;

use Awesome\Console\Model\CommandInterface;

class CommandFactory extends \Awesome\Framework\Model\AbstractFactory
{
    /**
     * Create command object.
     * @param string $commandId
     * @return CommandInterface
     * @throws \Exception
     */
    public function create(string $commandId): CommandInterface
    {
        return $this->invoker->create($commandId);
    }
}
