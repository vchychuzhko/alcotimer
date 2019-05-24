<?php

namespace Ava\Console;

class Console
{
    private const COMMAND_NAMESPACE = 'Ava\Console\Command';
    private const HELP_SUGGESTION = 'Try run `php bin/console` to see possible commands.';

    /**
     * @var array $args
     */
    private $args;

    /**
     * @var array $classMap
     */
    protected $classMap = [
        'c' => 'Cache',
        'ca' => 'Cache',
        'cac' => 'Cache',
        'cach' => 'Cache',
        'cache' => 'Cache'
    ];

    /**
     * Console constructor.
     */
    public function __construct()
    {
        $this->args = $_SERVER['argv'];
    }

    /**
     * @return string
     */
    public function run()
    {
        if (isset($this->args[1])) {
            $args = explode(':', $this->args[1]);

            if ($className = $this->getClassName($args[0])) {
                /** @var \Ava\Console\AbstractCommand $class */
                $class = new $className();

                if ($method = $class->getMethodName($args[1])) {
                    $output = $class->{$method}();
                } else {
                    $output = 'Method `' . $args[1] . '` is not defined in ' . $className . "\n"
                        . self::HELP_SUGGESTION;
                }
            } else {
                $output = '`' . $args[0] . '` does not exist in ' . self::COMMAND_NAMESPACE . "\n"
                    . self::HELP_SUGGESTION;
            }
        } else {
            $output = $this->showInfo();
        }
        return $output . "\n";
    }

    /**
     * @return string
     */
    private function showInfo() {
        return <<<HTML
 --- AlcoTimer CLI ---
Here is the list of available commands:
`php bin/console cache:clean` | Clean and regenerate static files, forcing browser to reload JS and CSS. 
HTML;
    }

    /**
     * @param $className string
     * @return string
     */
    private function getClassName($className) {
        return isset($this->classMap[$className])
            ? '\\' . self::COMMAND_NAMESPACE . '\\' . $this->classMap[$className]
            : '';
    }
}
