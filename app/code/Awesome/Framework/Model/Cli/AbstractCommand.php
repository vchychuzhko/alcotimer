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
        return $definition->addOption('help', 'h', InputDefinition::OPTION_OPTIONAL, 'Display help message')
            ->addOption('no-interaction', 'n', InputDefinition::OPTION_OPTIONAL, 'Do not ask any interactive question')
            ->addOption('quiet', 'q', InputDefinition::OPTION_OPTIONAL, 'Do not output anything')
            ->addOption('version', 'v', InputDefinition::OPTION_OPTIONAL, 'Display application version');
    }

    /**
     * Run the console command.
     * @param Input $input
     * @param Output $output
     * @return string
     */
    abstract public function execute($input, $output);
}
