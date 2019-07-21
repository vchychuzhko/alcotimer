<?php

namespace Awesome\Console\Model;

abstract class AbstractCommand
{
    /**
     * More info here: https://joshtronic.com/2013/09/02/how-to-use-colors-in-command-line-output
     * @var array $colours
     */
    private $colours = [
        'red' => '0;31',
        'light-red' => '1;31',
        'green' => '0;32',
        'light-green' => '1;32'
    ];

    /**
     * @param array $args
     * @return string
     */
    abstract function execute($args = []);

    /**
     * Parse input arguments.
     * @param array $args
     * @return array
     */
    protected function parseArguments($args) {
        $arguments = [];

        foreach ($args as $arg) {
            @list($argument, $value) = explode('=', str_replace('--', '', $arg));
            $arguments[$argument][] = $value;
        }

        return $arguments;
    }

    /**
     * Wrap text with colour for CLI.
     * @param string $text
     * @param string $colour
     * @return string
     */
    protected function colourText($text, $colour = 'green') {
        if (DS !== '\\' && isset($this->colours[$colour])) {
            $text = "\033[" . $this->colours[$colour] . "m" . $text . "\033[0m";
        }

        return $text;
    }
}
