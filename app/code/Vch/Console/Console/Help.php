<?php
declare(strict_types=1);

namespace Vch\Console\Console;

use Vch\Console\Exception\NoSuchCommandException;
use Vch\Console\Model\AbstractCommand;
use Vch\Console\Model\Cli;
use Vch\Console\Model\Cli\CommandResolver;
use Vch\Console\Model\Cli\Input;
use Vch\Console\Model\Cli\Input\InputDefinition;
use Vch\Console\Model\Cli\Output;
use Vch\Console\Model\CommandInterface;
use Vch\Framework\Helper\DataHelper;

class Help extends \Vch\Console\Model\AbstractCommand
{
    /**
     * @var CommandResolver $commandResolver
     */
    private $commandResolver;

    /**
     * Help constructor.
     * @param CommandResolver $commandResolver
     */
    public function __construct(CommandResolver $commandResolver)
    {
        $this->commandResolver = $commandResolver;
    }

    /**
     * @inheritDoc
     */
    public static function configure(): InputDefinition
    {
        return parent::configure()
            ->setDescription('Show application help')
            ->addArgument('command', InputDefinition::ARGUMENT_OPTIONAL, 'Command to show help about');
    }

    /**
     * Show application or command specific help.
     * @inheritDoc
     * @throws \Exception
     */
    public function execute(Input $input, Output $output)
    {
        if ($commandName = $input->getArgument('command')) {
            $commandName = $this->commandResolver->parseCommand($commandName);

            if (!$command = $this->commandResolver->getCommand($commandName)) {
                throw new NoSuchCommandException($commandName);
            }

            $command->help($input, $output);
        } else {
            $output->writeln('AlcoTimer CLI ' . $output->colourText(Cli::VERSION));
            $output->writeln();

            $output->writeln($output->colourText('Usage:', Output::BROWN));
            $output->writeln('command [options] [arguments]', 2);
            $output->writeln();
            $definition = AbstractCommand::configure();

            if ($options = $definition->getOptions()) {
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
                $output->writeln();
            }

            if ($commands = $this->commandResolver->getCommands()) {
                $this->processCommands($commands, $output);
            } else {
                $output->writeln('No commands are currently available.');
            }
        }
    }

    /**
     * Display commands list.
     * @param array $commands
     * @param Output $output
     * @throws \Exception
     */
    private function processCommands(array $commands, Output $output)
    {
        $output->writeln($output->colourText('Available commands:', Output::BROWN));
        $padding = DataHelper::getMaxLength($commands, static function ($name) {
            list($unused, $command) = explode(':', $name);

            return strlen((string) $command);
        }) + 2;
        $lastNamespace = null;

        foreach ($commands as $commandName) {
            list($namespace, $name) = explode(':', $commandName);
            /** @var CommandInterface $command */
            $command = $this->commandResolver->getCommandClass($commandName);
            $definition = $command::configure();

            if ($namespace !== $lastNamespace) {
                $output->writeln($output->colourText($namespace, Output::BROWN), 1);
                $lastNamespace = $namespace;
            }

            $output->writeln($output->colourText(str_pad($name, $padding)) . $definition->getDescription(), 2);
        }
    }
}
