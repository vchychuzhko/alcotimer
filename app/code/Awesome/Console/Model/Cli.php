<?php

namespace Awesome\Console\Model;

use Awesome\Console\Console\Help;
use Awesome\Console\Model\Cli\AbstractCommand;
use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Console\Model\Handler\Command as CommandHandler;

class Cli
{
    public const VERSION = '0.3.1';
    public const DEFAULT_COMMAND = 'help:show';

    /**
     * @var CommandHandler $commandHandler
     */
    private $commandHandler;

    /**
     * @var Help $help
     */
    private $help;

    /**
     * @var Output $output
     */
    private $output;

    /**
     * @var Input $input
     */
    private $input;

    /**
     * Console app constructor.
     */
    public function __construct()
    {
        $this->commandHandler = new CommandHandler();
        $this->help = new Help();
        $this->output = new Output();
    }

    /**
     * Run the CLI application.
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public function run()
    {
        $input = $this->getInput();

        if (($command = $input->getCommand()) && !$this->commandHandler->commandExist($command)) {
            $e = new \RuntimeException(sprintf('Command "%s" is not defined', $command));
            $this->displayException($e);

            if ($candidates = $this->commandHandler->getAlternatives($command, false)) {
                $this->output->writeln('Did you mean one of these?', 2);

                foreach ($candidates as $candidate) {
                    $this->output->writeln($this->output->colourText($candidate, Output::BROWN), 4);
                }
            } else {
                $this->output->writeln('Try running application help, to see available commands.');
            }

            throw $e;
        }

        try {
            if ($this->isQuiet()) {
                $this->output->mute();
            }

            if ($this->isNonInteractive()) {
                $input->disableInteraction();
            }

            if ($this->showVersion()) {
                $this->showAppCliTitle();
            } elseif ($this->showCommandHelp()) {
                $this->help->execute($input, $this->output);
            } elseif ($command && $className = $this->commandHandler->getCommandClass($command)) {
                /** @var AbstractCommand $consoleClass */
                $consoleClass = new $className();
                $consoleClass->execute($input, $this->output);
            } else {
                $this->showAppCliTitle();
                $this->output->writeln();
                $this->help->execute($input, $this->output);
            }
        } catch (\LogicException | \RuntimeException $e) {
            $this->displayException($e);

            throw $e;
        }
    }

    /**
     * Display formatted exception message.
     * @param \Exception $e
     */
    private function displayException($e)
    {
        if ($length = strlen($e->getMessage())) {
            $this->output->writeln();
            $this->output->writeln($this->output->colourText(str_repeat(' ', $length + 4), Output::WHITE, Output::RED_BG));
            $this->output->writeln($this->output->colourText(
                str_repeat(' ', 1) . str_pad(get_class($e) . ':', $length + 3),
                Output::WHITE,
                Output::RED_BG
            ));
            $this->output->writeln($this->output->colourText(
                str_repeat(' ', 2) . str_pad($e->getMessage(), $length + 2),
                Output::WHITE,
                Output::RED_BG
            ));
            $this->output->writeln($this->output->colourText(str_repeat(' ', $length + 4), Output::WHITE, Output::RED_BG));
            $this->output->writeln();
        }
    }

    /**
     * Determine if output should be disabled.
     * @return bool
     */
    private function isQuiet()
    {
        return $this->getInput()->getOption('quiet');
    }

    /**
     * Determine if user interaction should be disabled.
     * @return bool
     */
    private function isNonInteractive()
    {
        return $this->getInput()->getOption('no-interaction');
    }

    /**
     * Determine if application version should be shown.
     * @return bool
     */
    private function showVersion()
    {
        return $this->getInput()->getOption('version');
    }

    /**
     * Determine if command help should be shown.
     * @return bool
     */
    private function showCommandHelp()
    {
        return $this->getInput()->getOption('help')
            && ($this->getInput()->getCommand() || $this->getInput()->getArgument('command'));
    }

    /**
     * Output application CLI title with version.
     */
    private function showAppCliTitle()
    {
        $this->output->writeln('AlcoTimer CLI ' . $this->output->colourText(self::VERSION));
    }

    /**
     * Parse and get console input.
     * @return Input
     */
    private function getInput()
    {
        if (!$this->input) {
            $argv = $_SERVER['argv'];
            $command = null;

            if (isset($argv[1]) && strpos($argv[1], '-') !== 0) {
                $command = $this->commandHandler->parseCommand($argv[1]);
                unset($argv[1]);
            }

            if ($command && !$this->commandHandler->commandExist($command)) {
                $this->input = new Input($command);
            } else {
                $options = [];
                $arguments = [];
                $collectedArguments = [];
                $argumentPosition = 1;

                $commandData = $this->commandHandler->getCommandData($command ?: self::DEFAULT_COMMAND);
                $commandOptions = $commandData['options'];
                $commandShortcuts = $commandData['shortcuts'];
                $commandArguments = $commandData['arguments'];

                foreach (array_slice($argv, 1) as $arg) {
                    if (strpos($arg, '--') === 0) {
                        @list($option, $value) = explode('=', str_replace_first('--', '', $arg));

                        if (!isset($commandOptions[$option])) {
                            throw new \RuntimeException(sprintf('Unknown option "%s"', $option));
                        }
                        $value = $value ?: $commandOptions[$option]['default'];

                        if ($commandOptions[$option]['type'] === InputDefinition::OPTION_ARRAY) {
                            if (!isset($options[$option])) {
                                $options[$option] = [];
                            }
                            $options[$option][] = $value;
                        } else {
                            $options[$option] = $value;
                        }
                    } elseif (strpos($arg, '-') === 0) {
                        $shortcuts = substr($arg, 1);

                        foreach (str_split($shortcuts) as $shortcut) {
                            if (!isset($commandShortcuts[$shortcut])) {
                                throw new \RuntimeException(sprintf('Unknown shortcut "%s"', $shortcut));
                            }
                            $option = $commandShortcuts[$shortcut];
                            $options[$option] = $commandOptions[$option]['default'];
                        }
                    } else {
                        $collectedArguments[$argumentPosition++] = $arg;
                    }
                }

                if ($commandOptions) {
                    foreach ($commandOptions as $optionName => $optionData) {
                        if ($optionData['type'] === InputDefinition::OPTION_REQUIRED && !isset($options[$optionName])) {
                            throw new \RuntimeException(sprintf('Required option "%s" was not provided', $optionName));
                        }
                    }
                }

                if ($commandArguments) {
                    foreach ($commandArguments as $argumentName => $argumentData) {
                        $position = $argumentData['position'];

                        if ($argumentData['type'] === InputDefinition::ARGUMENT_REQUIRED
                            && !isset($collectedArguments[$position])
                        ) {
                            throw new \RuntimeException(sprintf('Required argument "%s" was not provided', $argumentName));
                        } elseif ($argumentData['type'] === InputDefinition::ARGUMENT_ARRAY) {
                            $arguments[$argumentName] = array_slice($collectedArguments, $position - 1);
                        } elseif (isset($collectedArguments[$position])) {
                            $arguments[$argumentName] = $collectedArguments[$position];
                        }
                    }
                } else {
                    $arguments = $collectedArguments;
                }

                $this->input = new Input($command, $options, $arguments);
            }
        }

        return $this->input;
    }
}
