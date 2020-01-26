<?php

namespace Awesome\Console\Model;

abstract class AbstractCommand
{
    /**
     * More info here: https://joshtronic.com/2013/09/02/how-to-use-colors-in-command-line-output
     * @var array $colours
     */
    protected $colours = [
        'black' => '0;30',
        'dark-grey' => '1;30',
        'red' => '0;31',
        'light-red' => '1;31',
        'green' => '0;32',
        'light-green' => '1;32',
        'brown' => '0;33',
        'yellow' => '1;33',
        'blue' => '0;34',
        'light-blue' => '1;34',
        'magenta' => '0;35',
        'light-magenta' => '1;35',
        'cyan' => '0;36',
        'light-cyan' => '1;36',
        'light-grey' => '0;37',
        'white' => '1;37'
    ];

    /**
     * More info here: https://joshtronic.com/2013/09/02/how-to-use-colors-in-command-line-output
     * @var array $backgroundColours
     */
    protected $backgroundColours = [
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light-grey' => '47'
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
     * @param string $backgroundColour
     * @return string
     */
    public function colourText($text, $colour = 'green', $backgroundColour = '') {
        if (DS !== '\\' && isset($this->colours[$colour])) {
            $background = '';

            if ($backgroundColour && isset($this->backgroundColours[$backgroundColour])) {
                $background = ';' . $this->backgroundColours[$backgroundColour];
            }

            $text = "\033[" . $this->colours[$colour] . $background . "m" . $text . "\033[0m";
        }

        return $text;
    }
}
