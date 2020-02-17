<?php

namespace Awesome\Framework\Model\Cli;

class Output
{
    /**
     * More info here: https://joshtronic.com/2013/09/02/how-to-use-colors-in-command-line-output
     * @var array $colours
     */
    private $colours = [
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
    private $backgroundColours = [
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
     * @var bool $mute
     */
    private $mute = false;

    /**
     * Output the text.
     * @param string $text
     * @param int $indent
     * @return $this
     */
    public function write($text = '', $indent = 0)
    {
        if (!$this->mute) {
            echo str_repeat(' ', $indent) . $text;
        }

        return $this;
    }

    /**
     * Output the text with new line.
     * @param string $text
     * @param int $indent
     * @return $this
     */
    public function writeln($text = '', $indent = 0)
    {
        return $this->write($text . "\n", $indent);
    }

    /**
     * Disable output.
     * @return $this
     */
    public function mute()
    {
        $this->mute = true;

        return $this;
    }

    /**
     * Enable output.
     * @return $this
     */
    public function unmute()
    {
        $this->mute = false;

        return $this;
    }

    /**
     * Show progress bar.
     * Based on https://gist.github.com/mayconbordin/2860547
     * @param int $done
     * @param int $total
     * @param string $info
     * @param int $width
     * @return $this
     */
    public function progress($done, $total, $info = '', $width = 50) {
        //@TODO: Update to handle several progress bars at the same time (ProgressFactory?)
        $percentage = floor(($done * 100) / $total);
        $bar = floor(($width * $percentage) / 100);

        $carriage = $percentage === 100 ? '' : "\r";

        return $this->write(str_pad($percentage, 3) . '%[' . str_repeat('=', $bar)
            . '>' . str_repeat(' ', $width - $bar) . ']' . $info . $carriage);
    }

    /**
     * Wrap text with colour for CLI.
     * @param string $text
     * @param string $colour
     * @param string $backgroundColour
     * @return string
     */
    public function colourText($text, $colour = 'green', $backgroundColour = '') {
        //@TODO: move all colours to constants
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
