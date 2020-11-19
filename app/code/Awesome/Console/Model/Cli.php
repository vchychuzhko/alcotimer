<?php
declare(strict_types=1);

namespace Awesome\Console\Model;

use Awesome\Console\Console\Help;
use Awesome\Console\Exception\NoSuchCommandException;
use Awesome\Console\Model\Cli\AbstractCommand;
use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Console\Model\Handler\CommandHandler;
use Awesome\Framework\Exception\XmlValidationException;
use Awesome\Framework\Model\Invoker;

class Cli
{
    public const VERSION = '0.4.3';
    public const HELP_COMMAND = 'help:show';

    /**
     * @var CommandHandler $commandHandler
     */
    private $commandHandler;

    /**
     * @var Help $help
     */
    private $help;

    /**
     * @var Invoker $invoker
     */
    private $invoker;

    /**
     * @var Input $input
     */
    private $input;

    /**
     * @var Output $output
     */
    private $output;

    /**
     * Console app constructor.
     * @param CommandHandler $commandHandler
     * @param Help $help
     * @param Invoker $invoker
     */
    public function __construct(
        CommandHandler $commandHandler,
        Help $help,
        Invoker $invoker
    ) {
        $this->commandHandler = $commandHandler;
        $this->help = $help;
        $this->invoker = $invoker;
    }

    /**
     * Run the CLI application.
     * @return void
     * @throws \Exception
     */
    public function run(): void
    {
        try {
            $input = $this->getInput();
            $output = $this->getOutput();

            if (($command = $input->getCommand()) && !$this->commandHandler->commandExist($command)) {
                throw new NoSuchCommandException($command);
            }

            if ($this->showVersion()) {
                $this->showAppCliTitle();
            } elseif ($command && $className = $this->commandHandler->getCommandClass($command)) {
                /** @var AbstractCommand $consoleClass */
                $consoleClass = $this->invoker->get($className);

                if ($this->showCommandHelp()) {
                    $consoleClass->help($input, $output);
                } else {
                    $consoleClass->execute($input, $output);
                }
            } else {
                $this->showAppCliTitle();
                $output->writeln();
                $this->help->execute($input, $output);
            }
        } catch (NoSuchCommandException $e) {
            $this->displayException($e);

            if ($candidates = $this->commandHandler->getAlternatives($e->getCommand(), false)) {
                $this->getOutput()->writeln('Did you mean one of these?', 2);

                foreach ($candidates as $candidate) {
                    $this->getOutput()->writeln($this->getOutput()->colourText($candidate, Output::BROWN), 4);
                }
            } else {
                $this->getOutput()->writeln('Try running application help, to see available commands.');
            }

            exit(1);
        } catch (\InvalidArgumentException | XmlValidationException $e) {
            $this->displayException($e);

            exit(1);
        }
    }

    /**
     * Display formatted exception message.
     * @param \Exception $e
     * @return void
     */
    private function displayException(\Exception $e): void
    {
        $name = get_class_name($e);
        $message = $e->getMessage();
        $length = max(strlen($name), strlen($message));

        $this->getOutput()->writeln();
        $this->getOutput()->writeln($this->getOutput()->colourText(str_repeat(' ', $length + 4), Output::WHITE, Output::RED_BG));
        $this->getOutput()->writeln($this->getOutput()->colourText(
            str_repeat(' ', 1) . str_pad($name . ':', $length + 3),
            Output::WHITE,
            Output::RED_BG
        ));
        $this->getOutput()->writeln($this->getOutput()->colourText(
            str_repeat(' ', 2) . str_pad($message, $length + 2),
            Output::WHITE,
            Output::RED_BG
        ));
        $this->getOutput()->writeln($this->getOutput()->colourText(str_repeat(' ', $length + 4), Output::WHITE, Output::RED_BG));
        $this->getOutput()->writeln();
    }

    /**
     * Determine if output should be disabled.
     * @return bool
     */
    private function isQuiet(): bool
    {
        return (bool) $this->getInput()->getOption(AbstractCommand::QUIET_OPTION);
    }

    /**
     * Determine if user interaction should be disabled.
     * @return bool
     */
    private function isNonInteractive(): bool
    {
        return (bool) $this->getInput()->getOption(AbstractCommand::NOINTERACTION_OPTION);
    }

    /**
     * Determine if application version should be shown.
     * @return bool
     */
    private function showVersion(): bool
    {
        return (bool) $this->getInput()->getOption(AbstractCommand::VERSION_OPTION);
    }

    /**
     * Determine if command help should be shown.
     * @return bool
     */
    private function showCommandHelp(): bool
    {
        return (bool) $this->getInput()->getOption(AbstractCommand::HELP_OPTION);
    }

    /**
     * Output application CLI title with version.
     * @return void
     */
    private function showAppCliTitle(): void
    {
        $this->getOutput()->writeln('AlcoTimer CLI ' . $this->getOutput()->colourText(self::VERSION));
    }

    /**
     * Parse and get CLI input.
     * @return Input
     * @throws \InvalidArgumentException
     */
    private function getInput(): Input
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

                $commandData = $this->commandHandler->getCommandData($command ?: self::HELP_COMMAND);
                $commandOptions = $commandData['options'];
                $commandShortcuts = $commandData['shortcuts'];
                $commandArguments = $commandData['arguments'];

                foreach (array_slice($argv, 1) as $arg) {
                    if (strpos($arg, '--') === 0) {
                        @list($option, $value) = explode('=', str_replace_first('--', '', $arg));

                        if (!isset($commandOptions[$option])) {
                            throw new \InvalidArgumentException(sprintf('Unknown option "%s"', $option));
                        }
                        $value = $value ?: $commandOptions[$option]['default'];

                        if ($commandOptions[$option]['type'] === InputDefinition::OPTION_ARRAY) {
                            $options[$option] = $options[$option] ?? [];
                            $options[$option][] = $value;
                        } else {
                            $options[$option] = $value;
                        }
                    } elseif (strpos($arg, '-') === 0) {
                        $shortcuts = substr($arg, 1);

                        foreach (str_split($shortcuts) as $shortcut) {
                            if (!isset($commandShortcuts[$shortcut])) {
                                throw new \InvalidArgumentException(sprintf('Unknown shortcut "%s"', $shortcut));
                            }
                            $option = $commandShortcuts[$shortcut];
                            $options[$option] = $commandOptions[$option]['default'];
                        }
                    } else {
                        $collectedArguments[$argumentPosition++] = $arg;
                    }
                }

                if (!isset($options[AbstractCommand::HELP_OPTION]) && !isset($options[AbstractCommand::VERSION_OPTION])) {
                    if ($commandOptions) {
                        foreach ($commandOptions as $optionName => $optionData) {
                            if ($optionData['type'] === InputDefinition::OPTION_REQUIRED && !isset($options[$optionName])) {
                                throw new \InvalidArgumentException(sprintf('Required option "%s" was not provided', $optionName));
                            }
                        }
                    }

                    if ($commandArguments) {
                        foreach ($commandArguments as $argumentName => $argumentData) {
                            $position = $argumentData['position'];

                            if ($argumentData['type'] === InputDefinition::ARGUMENT_REQUIRED
                                && !isset($collectedArguments[$position])
                            ) {
                                throw new \InvalidArgumentException(sprintf('Required argument "%s" was not provided', $argumentName));
                            } elseif ($argumentData['type'] === InputDefinition::ARGUMENT_ARRAY) {
                                $arguments[$argumentName] = array_slice($collectedArguments, $position - 1);
                            } elseif (isset($collectedArguments[$position])) {
                                $arguments[$argumentName] = $collectedArguments[$position];
                            }
                        }
                    } else {
                        $arguments = $collectedArguments;
                    }
                }

                $this->input = new Input($command, $options, $arguments);
            }
        }

        return $this->input;
    }

    /**
     * Get CLI output.
     * @return Output
     */
    private function getOutput(): Output
    {
        if (!$this->output) {
            if ($this->input) {
                $this->output = new Output(!$this->isQuiet() && !$this->isNonInteractive(), $this->isQuiet());
            } else {
                $this->output = new Output();
            }
        }

        return $this->output;
    }
}
