<?php
declare(strict_types=1);

namespace Awesome\Console\Model;

use Awesome\Console\Console\Help;
use Awesome\Console\Exception\NoSuchCommandException;
use Awesome\Console\Model\Cli\CommandResolver;
use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Framework\Exception\XmlValidationException;

class Cli
{
    public const VERSION = '0.6.1';

    /**
     * @var CommandResolver $commandResolver
     */
    private $commandResolver;

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
     * @param CommandResolver $commandResolver
     */
    public function __construct(
        CommandResolver $commandResolver
    ) {
        $this->commandResolver = $commandResolver;
    }

    /**
     * Run the CLI application.
     * @throws \Exception
     */
    public function run()
    {
        try {
            $input = $this->getInput();
            $output = $this->getOutput();
            $commandName = $input->getCommandName();

            if ($this->showVersion()) {
                $this->showAppCliTitle();
            } elseif ($commandName && $command = $this->commandResolver->getCommand($commandName)) {
                if ($this->showCommandHelp()) {
                    $command->help($input, $output);
                } else {
                    $command->execute($input, $output);
                }
            } else {
                /** @var Help $help */
                $help = $this->commandResolver->getHelpCommand();

                $help->execute($input, $output);
            }
        } catch (NoSuchCommandException $e) {
            exit(1);
        } catch (\InvalidArgumentException | XmlValidationException $e) {
            $this->displayException($e);

            exit(1);
        }
    }

    /**
     * Display formatted exception message.
     * @param \Exception $e
     */
    private function displayException(\Exception $e)
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
     * Check if output should be disabled.
     * @return bool
     */
    private function isQuiet(): bool
    {
        return $this->input && $this->input->getOption(AbstractCommand::QUIET_OPTION);
    }

    /**
     * Check if user interaction should be disabled.
     * @return bool
     */
    private function isNonInteractive(): bool
    {
        return $this->input && $this->input->getOption(AbstractCommand::NOINTERACTION_OPTION);
    }

    /**
     * Check if application version should be shown.
     * @return bool
     */
    private function showVersion(): bool
    {
        return $this->input && $this->input->getOption(AbstractCommand::VERSION_OPTION);
    }

    /**
     * Check if command help should be shown.
     * @return bool
     */
    private function showCommandHelp(): bool
    {
        return $this->input && $this->input->getOption(AbstractCommand::HELP_OPTION);
    }

    /**
     * Output application CLI title with version.
     */
    private function showAppCliTitle()
    {
        $this->getOutput()->writeln('AlcoTimer CLI ' . $this->getOutput()->colourText(self::VERSION));
    }

    /**
     * Parse and get CLI input.
     * @return Input
     * @throws \Exception
     */
    private function getInput(): Input
    {
        if (!$this->input) {
            $argv = $_SERVER['argv'];
            $commandName = null;

            if (isset($argv[1]) && strpos($argv[1], '-') !== 0) {
                $commandName = $this->commandResolver->parseCommand($argv[1]);
                unset($argv[1]);
            }

            if ($commandName) {
                if (!$this->commandResolver->commandExist($commandName)) {
                    $e = new NoSuchCommandException(__('Command "%1" was not recognized', $commandName));
                    $this->displayException($e);

                    if ($alternatives = $this->commandResolver->getAlternatives($commandName, false)) {
                        $this->getOutput()->writeln('Did you mean one of these?', 2);

                        foreach ($alternatives as $alternative) {
                            $this->getOutput()->writeln($this->getOutput()->colourText($alternative, Output::BROWN), 4);
                        }
                    } else {
                        $this->getOutput()->writeln('Try running application help, to see available commands.');
                    }

                    throw $e;
                }
                $command = $this->commandResolver->getCommandClass($commandName) ?: Help::class;
            } else {
                $command = Help::class;
            }
            $options = [];
            $arguments = [];
            $collectedArguments = [];
            $argumentPosition = 1;

            /** @var CommandInterface $command */
            $definition = $command::configure();
            $commandOptions = $definition->getOptions();
            $commandShortcuts = $definition->getShortcuts();
            $commandArguments = $definition->getArguments();

            foreach (array_slice($argv, 1) as $arg) {
                if (strpos($arg, '--') === 0) {
                    @list($option, $value) = explode('=', str_replace_first('--', '', $arg));

                    if (!isset($commandOptions[$option])) {
                        throw new \InvalidArgumentException(__('Unknown option "%1"', $option));
                    }
                    $value = $value ?: $commandOptions[$option]['default'];

                    if ($commandOptions[$option]['type'] === InputDefinition::OPTION_ARRAY) {
                        $options[$option] = $options[$option] ?? [];
                        $options[$option][] = $value;
                    } else {
                        $options[$option] = $value;
                    }
                } elseif (strpos($arg, '-') === 0) {
                    foreach (str_split(substr($arg, 1)) as $shortcut) {
                        if (!isset($commandShortcuts[$shortcut])) {
                            throw new \InvalidArgumentException(__('Unknown shortcut "%1"', $shortcut));
                        }
                        $option = $commandShortcuts[$shortcut];
                        $options[$option] = $commandOptions[$option]['default'];
                    }
                } else {
                    $collectedArguments[$argumentPosition++] = $arg;
                }
            }

            if (!isset($options[AbstractCommand::HELP_OPTION]) && !isset($options[AbstractCommand::VERSION_OPTION])) {
                foreach ($commandOptions as $optionName => $optionData) {
                    if ($optionData['type'] === InputDefinition::OPTION_REQUIRED && !isset($options[$optionName])) {
                        throw new \InvalidArgumentException(__('Required option "%1" was not provided', $optionName));
                    }
                }

                foreach ($commandArguments as $argumentName => $argumentData) {
                    $position = $argumentData['position'];

                    if ($argumentData['type'] === InputDefinition::ARGUMENT_REQUIRED
                        && !isset($collectedArguments[$position])
                    ) {
                        throw new \InvalidArgumentException(__('Required argument "%1" was not provided', $argumentName));
                    }
                    if ($argumentData['type'] === InputDefinition::ARGUMENT_ARRAY) {
                        $arguments[$argumentName] = array_slice($collectedArguments, $position - 1);
                    } elseif (isset($collectedArguments[$position])) {
                        $arguments[$argumentName] = $collectedArguments[$position];
                    }
                }
            }

            $this->input = new Input($commandName, $options, $arguments);
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
