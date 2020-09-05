<?php

namespace Awesome\Console\Model\Cli;

use Awesome\Console\Console\Help;
use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;

abstract class AbstractCommand
{
    public const VERSION_OPTION = 'version';

    /**
     * Define all data related to console command.
     * @param InputDefinition $definition
     * @return InputDefinition
     * @throws \LogicException
     */
    public static function configure($definition)
    {
        return $definition->addOption(Help::HELP_OPTION, 'h', InputDefinition::OPTION_OPTIONAL, 'Display help message')
            ->addOption('no-interaction', 'n', InputDefinition::OPTION_OPTIONAL, 'Do not ask any interactive question')
            ->addOption('quiet', 'q', InputDefinition::OPTION_OPTIONAL, 'Do not output anything')
            ->addOption(self::VERSION_OPTION, 'V', InputDefinition::OPTION_OPTIONAL, 'Display application version');
    }

    /**
     * Run the console command.
     * @param Input $input
     * @param Output $output
     * @return void
     */
    abstract public function execute($input, $output);
}
