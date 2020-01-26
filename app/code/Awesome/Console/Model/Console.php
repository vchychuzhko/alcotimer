<?php

namespace Awesome\Console\Model;

class Console
{
    /**
     * @var \Awesome\Console\Model\XmlParser\CliXmlParser $xmlParser
     */
    private $xmlParser;

    /**
     * @var \Awesome\Console\Console\ShowHelp $help
     */
    private $help;

    /**
     * Console app constructor.
     */
    public function __construct()
    {
        $this->xmlParser = new \Awesome\Console\Model\XmlParser\CliXmlParser();
        $this->help = new \Awesome\Console\Console\ShowHelp();
    }

    /**
     * Run the CLI application.
     */
    public function run()
    {
        list($command, $options, $arguments) = $this->parseInput();

        if ($this->showVersion($options)) {
            $output = $this->help->getAppCliTitle();
        } elseif ($command) {
            $output = $this->help->colourText('Command "' . $command . '" is not defined.', 'white', 'red') . "\n";
            $className = $this->parseCommand($command);

            if ($className && !is_array($className)) {
                /** @var \Awesome\Console\Model\AbstractCommand $consoleClass */
                $consoleClass = new $className($options, $arguments);
                $output = $consoleClass->execute() . "\n";
            } elseif ($className) {
                $output .= "\n" . '  Did you mean one of these?' . "\n"
                    . $this->help->colourText(implode("\n", $className), 'brown') . "\n";
            }
        } else {
            $output = $this->help->execute() . "\n";
        }

        if ($this->isQuiet($options)) {
            $output = '';
        }

        echo $output;
    }

    /**
     * Parse console input into command, options and arguments.
     * Return array with the mentioned order.
     * @return array
     */
    private function parseInput()
    {
        $args = $_SERVER['argv'];
        $command = '';
        $options = [];
        $arguments = [];

        if (isset($args[1]) && strpos($args[1], '-') !== 0) {
            $command = $args[1];
        }

        foreach (array_slice($args, 1) as $arg) {
            if (strpos($arg, '--') === 0) {
                @list($option, $value) = explode('=', str_replace_first('--', '', $arg));
                $options[$option][] = $value ?: true;
            } elseif (strpos($arg, '-') === 0) {
                $option = substr($arg, 1, 1);
                $value = substr($arg, 2);
                $options[$option][] = $value ?: true;
            } else {
                $arguments[] = $arg;
            }
        }

        return [
            $command,
            $options,
            $arguments
        ];
    }

    /**
     * Get classname by called command.
     * @param string $input
     * @return string|array
     */
    private function parseCommand($input)
    {
        $className = '';
        @list($namespace, $command) = explode(':', $input);
        $consoleCommands = $this->xmlParser->retrieveConsoleCommands();

        if ($namespace = $this->findMatch($namespace, array_keys($consoleCommands))) {
            $command = $this->findMatch($command, array_keys($consoleCommands[$namespace]));

            if ($command && !$consoleCommands[$namespace][$command]['disabled']) {
                $className = $consoleCommands[$namespace][$command]['class'];
            } else  {
                $className = array_keys($consoleCommands[$namespace]);
                $className = array_map(function ($candidate) use ($namespace) {
                    return '    ' . $namespace . ':' . $candidate;
                }, $className);
            }
        }

        return $className;
    }

    /**
     * Find potential matched string in array by its part.
     * @param string $search
     * @param array $candidates
     * @return string
     */
    private function findMatch($search, $candidates)
    {
        $match = '';
        $possibleMatches = [];

        if ($search) {
            foreach ($candidates as $candidate) {
                if (strpos($candidate, $search) === 0) {
                    $possibleMatches[] = $candidate;
                }
            }

            if (count($possibleMatches) === 1) {
                $match = $possibleMatches[0];
            }
        }

        return $match;
    }

    /**
     * Determine if application version should be shown.
     * @param array $options
     * @return bool
     */
    private function showVersion($options)
    {
        return isset($options['v']) || isset($options['version']);
    }

    /**
     * Determine if output should be disabled.
     * @param $options
     * @return bool
     */
    private function isQuiet($options)
    {
        return isset($options['q']) || isset($options['quiet']);
    }
}
