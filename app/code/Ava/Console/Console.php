<?php

namespace Ava\Console;

class Console
{
    private const COMMAND_NAMESPACE = 'Ava\Console\Command';
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
        if (isset($this->args[1])) {
            list($classArg, $methodArg, $args) = $this->splitArgs();

            if ($className = $this->mapClassName($classArg)) {
                $class = new $className();

                if ($methodName = $this->mapMethodName($methodArg, $class)) {
                    $output = $class->{$methodName}($args);
                } else {
                    $output = 'Method `' . $methodArg . '` is not defined in ' . $className . "\n"
                        . self::HELP_SUGGESTION;
                }
            } else {
                $output = '`' . $classArg . '` does not exist in ' . self::COMMAND_NAMESPACE . "\n"
                    . self::HELP_SUGGESTION;
            }
        } else {
            $output = $this->showInfo();
        }

        echo $output . "\n";
    }

    /**
     * Parse console input into array of arguments.
     * @return array
     */
    private function splitArgs()
    {
        $path = explode(':', $this->args[1]);

        return [
            $path[0], //className
            $path[1] ?? '', //methodName
            array_slice($this->args, 2) ?? [] //additional arguments
        ];
    }

    /**
     * Check if called class exists and return its path.
     * @param string $className
     * @return string
     */
    private function mapClassName($className)
    {
        $dirPath = APP_DIR . DS . str_replace('\\', DS, self::COMMAND_NAMESPACE);
        $files = scandir($dirPath);
        $classes = [];

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $name = str_replace('.php', '', $file);
                $classes[] = strtolower($name);
            }
        }

        if ($matchClass = $this->findMatch($className, $classes)) {
            $className = '\\' . self::COMMAND_NAMESPACE . '\\' . ucfirst($matchClass);
        } else {
            $className = '';
        }

        return $className;
    }

    /**
     * Check if called method exists in class and return its name.
     * @param string $methodName
     * @param \Ava\Console\AbstractCommand $class
     * @return string
     */
    private function mapMethodName($methodName, $class)
    {
        $methods = get_class_methods($class);

        if ($matchMethod = $this->findMatch($methodName, $methods)) {
            $methodName = $matchMethod;
        } else {
            $methodName = '';
        }

        return $methodName;
    }

    /**
     * Show info in case when there is no arguments.
     * @return string
     */
    private function showInfo()
    {
        return <<<HTML
 --- AlcoTimer CLI ---
Here is the list of available commands:
`php bin/console cache:clean` | Clean and regenerate static files, forcing browser to reload JS and CSS.
 
`php bin/console maintenance:enable [--ip=<ip address>]` | Enable maintenance mode with list of allowed ids. 
`php bin/console maintenance:disable` | Disable maintenance mode. 
`php bin/console maintenance:status` | View current state of maintenance. 
HTML;
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

        foreach ($candidates as $candidate) {
            if (strpos($candidate, $search) === 0) {
                $possibleMatches[] = $candidate;
            }
        }

        if (count($possibleMatches) === 1) {
            $match = $possibleMatches[0];
        }

        return $match;
    }
}
