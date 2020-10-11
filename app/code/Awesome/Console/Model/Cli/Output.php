<?php

namespace Awesome\Console\Model\Cli;

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
     * @var bool $interactive
     */
    private $interactive;

    /**
     * @var bool $mute
     */
    private $mute;

    /**
     * Output constructor.
     * @param bool $interactive
     * @param bool $mute
     */
    public function __construct($interactive = true, $mute = false)
    {
        $this->interactive = $interactive;
        $this->mute = $mute;
    }

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
     * Output the text with a new line.
     * @param string $text
     * @param int $indent
     * @return $this
     */
    public function writeln($text = '', $indent = 0)
    {
        return $this->write($text . "\n", $indent);
    }

    /**
     * Read user input.
     * @param string $prompt
     * @return string
     */
    public function read($prompt = '')
    {
        $line = '';

        if ($this->interactive) {
            $this->write($prompt);
            $line = readline();
            readline_add_history($line);
        }

        return $line;
    }

    /**
     * Read user input starting with a new line.
     * @param string $prompt
     * @return string
     */
    public function readln($prompt = '')
    {
        return $this->read($prompt . "\n");
    }

    /**
     * Prompt user confirmation.
     * @param string $prompt
     * @param array $confirmOptions
     * @return bool
     */
    public function confirm($prompt, $confirmOptions = ['y', 'yes'])
    {
        $confirm = true;

        if ($this->interactive) {
            $answer = $this->readln($prompt);
            $confirm = in_array($answer, $confirmOptions, true);
        }

        return $confirm;
    }

    /**
     * Prompt user to select one of the provided options.
     * @param string $prompt
     * @param array $options
     * @return string
     */
    public function choice($prompt, $options)
    {
        $choice = null;

        if ($this->interactive && $options) {
            foreach ($options as $optionKey => $option) {
                $prompt .= "\n" . $optionKey . ': ' . $option;
            }
            $prompt .= "\n" . 'Your choice: ';
            $answer = $this->readln($prompt);

            if (array_key_exists($answer, $options)) {
                $choice = $answer;
            }
        }

        return $choice;
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
     * Disable interaction.
     * @return $this
     */
    public function disableInteraction()
    {
        $this->interactive = false;

        return $this;
    }

    /**
     * Enable interaction.
     * @return $this
     */
    public function enableInteraction()
    {
        $this->interactive = true;

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
     * @param string|null $backgroundColour
     * @return string
     */
    public function colourText($text, $colour = self::GREEN, $backgroundColour = null)
    {
        $backgroundColour = $backgroundColour ? ';' . $backgroundColour : '';

        return "\e[" . $colour . $backgroundColour . "m" . $text . "\e[0m";
    }

    /**
     * Make text bold for CLI.
     * @param string $text
     * @return string
     */
    public function bold($text)
    {
        return "\e[1m" . $text . "\e[0m";
    }

    /**
     * Style text with underline for CLI.
     * @param string $text
     * @return string
     */
    public function underline($text)
    {
        return "\e[4m" . $text . "\e[0m";
    }
}
