<?php
declare(strict_types=1);

namespace Vch\Console\Model\Cli;

use Vch\Console\Model\CommandInterface;

class CommandFactory extends \Vch\Framework\Model\AbstractFactory
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
