<?php
declare(strict_types=1);

namespace Awesome\Console\Console;

use Awesome\Console\Model\Cli\AbstractCommand;
use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Console\Model\Handler\CommandHandler;

class Help extends \Awesome\Console\Model\Cli\AbstractCommand
{
    /**
     * @var CommandHandler $commandHandler
     */
    private $commandHandler;

    /**
     * Help constructor.
     * @param CommandHandler $commandHandler
     */
    public function __construct(CommandHandler $commandHandler)
    {
        $this->commandHandler = $commandHandler;
    }

    /**
     * @inheritDoc
     */
    public static function configure(InputDefinition $definition): InputDefinition
    {
        return parent::configure($definition)
            ->setDescription('Show application help');
    }

    /**
     * Show application help.
     * @inheritDoc
     * @throws \LogicException
     */
    public function execute(Input $input, Output $output): void
    {
        $commandData = AbstractCommand::configure(new InputDefinition())
            ->getDefinition();

        $output->writeln($output->colourText('Usage:', Output::BROWN));
        $output->writeln('command [options] [arguments]', 2);
        $output->writeln();

        if ($options = $commandData['options']) {
            $output->writeln($output->colourText('Options:', Output::BROWN));
            $optionFullNames = [];

            foreach ($options as $name => $optionData) {
                if ($shortcut = $optionData['shortcut']) {
                    $optionFullNames[$name] = '-' . $shortcut . ', --' . $name;
                } else {
                    $optionFullNames[$name] = str_repeat(' ', 4) . '--' . $name;
                }
            }
            $padding = max(array_map(static function ($option) {
                return strlen($option);
            }, $optionFullNames));

            foreach ($options as $name => $option) {
                $output->writeln(
                    $output->colourText(str_pad($optionFullNames[$name], $padding + 2)) . $option['description'],
                    2
                );
            }
            $output->writeln();
        }

        if ($commands = $this->commandHandler->getCommands()) {
            $this->processCommands($commands, $output);
        } else {
            $output->writeln('No commands are currently available.');
        }
    }

    /**
     * Display commands list.
     * @param array $commands
     * @param Output $output
     * @return void
     * @throws \LogicException
     */
    private function processCommands(array $commands, Output $output): void
    {
        if ($commands) {
            $output->writeln($output->colourText('Available commands:', Output::BROWN));
            $padding = max(array_map(static function ($name) {
                list($unused, $command) = explode(':', $name);

                return strlen($command);
            }, $commands));
            $lastNamespace = null;

            foreach ($commands as $name) {
                list($namespace, $command) = explode(':', $name);
                $commandData = $this->commandHandler->getCommandData($name);

                if ($namespace !== $lastNamespace) {
                    $output->writeln($output->colourText($namespace, Output::BROWN), 1);
                }
                $lastNamespace = $namespace;

                $output->writeln(
                    $output->colourText(str_pad($command, $padding + 2)) . $commandData['description'],
                    2
                );
            }
        }
    }
}
