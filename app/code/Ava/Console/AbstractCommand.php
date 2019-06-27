<?php

namespace Ava\Console;

class AbstractCommand
{
    /**
     * @var array $colours
     */
    private $colours = [
        'red' => '0;31',
        'light-red' => '1;31',
        'green' => '0;32',
        'light-green' => '1;32'
    ];

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
        if (!(strncasecmp(PHP_OS, 'WIN', 3) == 0) && isset($this->colours[$colour])) {
            $text = "\033[" . $this->colours[$colour] . "m" . $text . "\033[0m";
        }

        return $text;
    }
}
