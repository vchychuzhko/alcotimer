<?php

namespace Awesome\Framework\Model\Cli;

class Output
{
    /**
     * Foreground and background CLI colour constants.
     * More info here: https://misc.flogisoft.com/bash/tip_colors_and_formatting
     */
    public const BLACK = '30';
    public const DARK_GREY = '90';
    public const RED = '31';
    public const LIGHT_RED = '91';
    public const GREEN = '32';
    public const LIGHT_GREEN = '92';
    public const BROWN = '33';
    public const YELLOW = '93';
    public const BLUE = '34';
    public const LIGHT_BLUE = '94';
    public const MAGENTA = '35';
    public const LIGHT_MAGENTA = '95';
    public const CYAN = '36';
    public const LIGHT_CYAN = '96';
    public const LIGHT_GREY = '37';
    public const WHITE = '97';

    public const BLACK_BG = '40';
    public const DARK_GRAY_BG = '100';
    public const RED_BG = '41';
    public const LIGHT_RED_BG = '101';
    public const GREEN_BG = '42';
    public const LIGHT_GREEN_BG = '102';
    public const YELLOW_BG = '43';
    public const LIGHT_YELLOW_BG = '103';
    public const BLUE_BG = '44';
    public const LIGHT_BLUE_BG = '104';
    public const MAGENTA_BG = '45';
    public const LIGHT_MAGENTA_BG = '105';
    public const CYAN_BG = '46';
    public const LIGHT_CYAN_BG = '106';
    public const LIGHT_GREY_BG = '47';
    public const WHITE_BG = '107';

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
    public function progress($done, $total, $info = '', $width = 50)
    {
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
    public function colourText($text, $colour = self::GREEN, $backgroundColour = null)
    {
       if (DS !== '\\') {
            $backgroundColour = $backgroundColour ? ';' . $backgroundColour : '';

            $text = "\e[" . $colour . $backgroundColour . "m" . $text . "\e[0m";
       }

        return $text;
    }
}
