<?php

namespace Awesome\Console\Model\Cli;

use Awesome\Console\Model\Cli;
use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;

abstract class AbstractCommand
{
    public const HELP_OPTION = 'help';
    public const VERSION_OPTION = 'version';

    /**
     * Define all data related to console command.
     * @param InputDefinition $definition
     * @return InputDefinition
     * @throws \LogicException
     */
    public static function configure($definition)
    {
        return $definition->addOption(self::HELP_OPTION, 'h', InputDefinition::OPTION_OPTIONAL, 'Display help message')
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

    /**
     * Display help for the command.
     * @param Input $input
     * @param Output $output
     * @return void
     */
    public function help($input, $output)
    {
        $command = $input->getCommand();
        $commandData = static::configure(new InputDefinition())
            ->getDefinition();

        $options = $commandData['options'];
        $argumentsString = '';

        if ($description = $commandData['description']) {
            $output->writeln($output->colourText('Description:', Output::BROWN));
            $output->writeln($commandData['description'], 2);
            $output->writeln();
        }

        if ($arguments = $commandData['arguments']) {
            foreach ($arguments as $name => $argument) {
                $argumentsString .= ' ';

                switch ($argument['type']) {
                    case InputDefinition::ARGUMENT_REQUIRED:
                        $argumentsString .= $name;
                        break;
                    case InputDefinition::ARGUMENT_OPTIONAL:
                        $argumentsString .= '[' . $name . ']';
                        break;
                    case InputDefinition::ARGUMENT_ARRAY:
                        $argumentsString .= '[' . $name . '...]';
                        break;
                }
            }
        }

        $output->writeln($output->colourText('Usage:', Output::BROWN));
        $output->writeln($command . ($options ? ' [options]' : '') . $argumentsString, 2);

        if ($arguments) {
            $output->writeln();
            $output->writeln($output->colourText('Arguments:', Output::BROWN));
            $padding = max(array_map(function ($name) {
                return strlen($name);
            }, array_keys($arguments)));

            foreach ($arguments as $name => $argument) {
                $output->writeln($output->colourText(str_pad($name, $padding + 2)) . $argument['description'], 2);
            }
        }

        if ($options) {
            $output->writeln();
            $output->writeln($output->colourText('Options:', Output::BROWN));
            $optionFullNames = [];

            foreach ($options as $name => $optionData) {
                if ($shortcut = $optionData['shortcut']) {
                    $optionFullNames[$name] = '-' . $shortcut . ', --' . $name;
                } else {
                    $optionFullNames[$name] = str_repeat(' ', 4) . '--' . $name;
                }
            }
            $padding = max(array_map(function ($option) {
                return strlen($option);
            }, $optionFullNames));

            foreach ($options as $name => $option) {
                $output->writeln(
                    $output->colourText(str_pad($optionFullNames[$name], $padding + 2)) . $option['description'],
                    2
                );
            }
        }
    }
}
