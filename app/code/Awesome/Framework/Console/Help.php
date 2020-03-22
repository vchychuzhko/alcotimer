<?php

namespace Awesome\Framework\Console;

use Awesome\Framework\Model\Cli\Input\InputDefinition;
use Awesome\Framework\Model\Cli\Output;
use Awesome\Framework\XmlParser\CliXmlParser;

class Help extends \Awesome\Framework\Model\Cli\AbstractCommand
{
    /**
     * @var CliXmlParser $xmlParser
     */
    private $cliXmlParser;

    /**
     * Help constructor.
     */
    public function __construct()
    {
        $this->cliXmlParser = new CliXmlParser();
    }

    /**
     * @inheritDoc
     */
    public static function configure($definition)
    {
        return parent::configure($definition)
            ->setDescription('Show application help')
            ->addArgument('command', InputDefinition::ARGUMENT_OPTIONAL, 'Command to show help about');
    }

    /**
     * Show help for the application or command if specified.
     * @inheritDoc
     * @throws \LogicException
     */
    public function execute($input, $output)
    {
        if ($command = $input->getArgument('command') ?: $input->getCommand()) {
            $this->showCommandHelp($command, $output);
        } else {
            $commandData = $this->cliXmlParser->getDefault();
            $commands = $this->cliXmlParser->getHandles();

            $output->writeln($output->colourText('Usage:', Output::BROWN));
            $output->writeln('command [options] [arguments]', 2);
            $output->writeln();

            $this->processOptions($commandData['options'], $output, !empty($commands));

            if ($commands) {
                $this->processCommands($commands, $output);
            } else {
                $output->writeln('No commands are currently available.');
            }
        }
    }

    /**
     * Display help for specified command.
     * @param string $command
     * @param Output $output
     * @throws \LogicException
     */
    private function showCommandHelp($command, $output)
    {
        if ($commandData = $this->cliXmlParser->get($command)) {
            $options = $commandData['options'];
            $argumentsString = '';

            $output->writeln($output->colourText('Description:', Output::BROWN));
            $output->writeln($commandData['description'], 2);
            $output->writeln();

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
            $output->writeln();

            $this->processArguments($arguments, $output, !empty($options));

            $this->processOptions($options, $output);
        } else {
            throw new \LogicException(
                sprintf('Cannot show help for "%s" command. Please, try specifying the full name.', $command)
            );
        }
    }

    /**
     * Display command arguments.
     * @param array $arguments
     * @param Output $output
     * @param bool $newLine
     */
    private function processArguments($arguments, $output, $newLine = false)
    {
        if ($arguments) {
            $output->writeln($output->colourText('Arguments:', Output::BROWN));
            $padding = max(array_map(function ($name) {
                return strlen($name);
            }, array_keys($arguments)));

            foreach ($arguments as $name => $argument) {
                $output->writeln($output->colourText(str_pad($name, $padding + 2)) . $argument['description'], 2);
            }

            if ($newLine) {
                $output->writeln();
            }
        }
    }

    /**
     * Display command options.
     * @param array $options
     * @param Output $output
     * @param bool $newLine
     */
    private function processOptions($options, $output, $newLine = false)
    {
        if ($options) {
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

            if ($newLine) {
                $output->writeln();
            }
        }
    }

    /**
     * Display commands list.
     * @param array $commands
     * @param Output $output
     * @param bool $newLine
     * @throws \LogicException
     */
    private function processCommands($commands, $output, $newLine = false)
    {
        if ($commands) {
            $output->writeln($output->colourText('Available commands:', Output::BROWN));
            $padding = max(array_map(function ($name) {
                list($unused, $command) = explode(':', $name);
                return strlen($command);
            }, $commands));
            $lastNamespace = null;

            foreach ($commands as $name) {
                list($namespace, $command) = explode(':', $name);
                $commandData = $this->cliXmlParser->get($name);

                if ($namespace !== $lastNamespace) {
                    $output->writeln($output->colourText($namespace, Output::BROWN), 1);
                }
                $lastNamespace = $namespace;

                $output->writeln(
                    $output->colourText(str_pad($command, $padding + 2)) . $commandData['description'],
                    2
                );
            }

            if ($newLine) {
                $output->writeln();
            }
        }
    }
}
