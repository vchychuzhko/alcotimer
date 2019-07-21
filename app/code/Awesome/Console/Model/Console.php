<?php

namespace Awesome\Console\Model;

class Console
{
    private const CLI_XML_PATH_PATTERN = '/*/*/etc/cli.xml';
    private const COMMAND_BASE = 'php bin/console';
    private const HELP_SUGGESTION = 'Try run `' . self::COMMAND_BASE . '` to see possible commands.';

    /**
     * @var array $args
     */
    private $args;

    /**
     * Console constructor.
     */
    public function __construct()
    {
        $this->args = $_SERVER['argv'];
    }

    /**
     * Execute the command.
     */
    public function run()
    {
        list($commandName, $commandArgs) = $this->parseInput();

        if ($commandName) {
            if ($className = $this->mapClassName($commandName)) {
                /** @var \Awesome\Console\Model\AbstractCommand $consoleClass */
                $consoleClass = new $className();
                $output = $consoleClass->execute($commandArgs);
            } else {
                $output = '`' . $commandName . '` command is not defined in this application' . "\n"
                    . self::HELP_SUGGESTION;
            }
        } else {
            //@TODO: implement help console command
            $help = new \Awesome\Console\Command\Help();
            $output = $help->show();
        }

        echo $output . "\n";
    }

    /**
     * Parse console input into array of arguments.
     * @return array
     */
    private function parseInput()
    {
        return [
            $this->args[1] ?? [], //commandName
            array_slice($this->args, 2) ?? [] //additionalArgs
        ];
    }

    /**
     * Get class namespace by called command.
     * @param string $commandName
     * @return string
     */
    private function mapClassName($commandName)
    {
        @list($namespace, $command) = explode(':', $commandName);
        $className = '';

        if ($namespace && $command) {
            $commandList = [];

            foreach (glob(APP_DIR . self::CLI_XML_PATH_PATTERN) as $cliXmlFile) {
                $cliData = simplexml_load_file($cliXmlFile);
                $foundNamespace = (string)$cliData['namespace'];

                if (!isset($commandList[$foundNamespace])) {
                    $commandList[$foundNamespace] = [];
                }

                foreach ($cliData->command as $commandNode) {
                    $commandList[$foundNamespace][(string)$commandNode['name']] = (string)$commandNode['class'];
                }
            }
            //@TODO: implement cache functionality for all cli commands

            $namespace = $this->findMatch($namespace, array_keys($commandList));
            $command = $this->findMatch($command, array_keys($commandList[$namespace]));

            if ($command) {
                $className = $commandList[$namespace][$command];
            }
        }

        return $className;
    }

    /**
     * Find corresponding string by its part.
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
}
