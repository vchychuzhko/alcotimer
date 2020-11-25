<?php
declare(strict_types=1);

namespace Awesome\Console\Model;

use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Framework\Helper\DataHelper;

abstract class AbstractCommand implements \Awesome\Console\Model\CommandInterface
{
    public const HELP_OPTION = 'help';
    public const NOINTERACTION_OPTION = 'no-interaction';
    public const QUIET_OPTION = 'quiet';
    public const VERSION_OPTION = 'version';

    /**
     * @inheritDoc
     * @throws \LogicException
     */
    public static function configure(): InputDefinition
    {
        return (new InputDefinition())->addOption(self::HELP_OPTION, 'h', InputDefinition::OPTION_OPTIONAL, 'Display help message')
            ->addOption(self::NOINTERACTION_OPTION, 'n', InputDefinition::OPTION_OPTIONAL, 'Do not ask any interactive question')
            ->addOption(self::QUIET_OPTION, 'q', InputDefinition::OPTION_OPTIONAL, 'Do not output anything')
            ->addOption(self::VERSION_OPTION, 'V', InputDefinition::OPTION_OPTIONAL, 'Display application version');
    }

    /**
     * @inheritDoc
     */
    public function help(Input $input, Output $output): void
    {
        $commandName = $input->getCommandName();
        $definition = static::configure();

        $options = $definition->getOptions();
        $argumentsString = '';

        if ($description = $definition->getDescription()) {
            $output->writeln($output->colourText('Description:', Output::BROWN));
            $output->writeln($description, 2);
            $output->writeln();
        }

        if ($arguments = $definition->getArguments()) {
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
        $output->writeln($commandName . ($options ? ' [options]' : '') . $argumentsString, 2);

        if ($arguments) {
            $output->writeln();
            $output->writeln($output->colourText('Arguments:', Output::BROWN));
            $padding = DataHelper::getMaxLength(array_keys($arguments)) + 2;

            foreach ($arguments as $name => $argument) {
                $output->writeln($output->colourText(str_pad($name, $padding)) . $argument['description'], 2);
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
            $padding = DataHelper::getMaxLength($optionFullNames) + 2;

            foreach ($options as $name => $option) {
                $output->writeln(
                    $output->colourText(str_pad($optionFullNames[$name], $padding)) . $option['description'],
                    2
                );
            }
        }
    }
}
