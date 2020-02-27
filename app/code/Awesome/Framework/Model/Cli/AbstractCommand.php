<?php

namespace Awesome\Framework\Model\Cli;

use Awesome\Framework\Model\Cli\Input;
use Awesome\Framework\Model\Cli\Input\InputDefinition;
use Awesome\Framework\Model\Cli\Output;

abstract class AbstractCommand
{
    /**
     * Define all data related to console command.
     * @param InputDefinition $definition
     * @return InputDefinition
     * @throws \LogicException
     */
    public static function configure($definition)
    {
        return $definition->addOption('help', 'h', InputDefinition::OPTION_OPTIONAL, 'Display this help message')
            ->addOption('quiet', 'q', InputDefinition::OPTION_OPTIONAL, 'Do not output any message')
            ->addOption('version', 'v', InputDefinition::OPTION_OPTIONAL, 'Display this application version')
            ->addOption('no-interaction', 'n', InputDefinition::OPTION_OPTIONAL, 'Do not ask any interactive questions');
    }

    /**
     * Run the console command.
     * @param Input $input
     * @param Output $output
     * @return string
     */
    abstract public function execute($input, $output);
}
