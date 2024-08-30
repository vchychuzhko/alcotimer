<?php
declare(strict_types=1);

namespace Vch\Console\Model\Cli\Output;

/**
 * Class ProgressBar
 * @link https://gist.github.com/mayconbordin/2860547
 */
class ProgressBar
{
    /**
     * @var int $total
     */
    private $total;

    /**
     * @var int $progress
     */
    private $progress;

    /**
     * @var int $width
     */
    private $width;

    /**
     * @var bool $mute
     */
    private $mute;

    /**
     * ProgressBar constructor.
     * @param int $total
     * @param int $width
     * @param bool $mute
     */
    public function __construct(int $total, int $width = 50, bool $mute = false)
    {
        $this->total = $total;
        $this->width = $width;
        $this->mute = $mute;
    }

    /**
     * Start progress bar with initial positions.
     * @param string $title
     * @return $this
     */
    public function init(string $title = ''): self
    {
        if ($title) {
            $this->write($title, true);
        }
        $this->progress = 0;
        $this->writeProgress(0);

        return $this;
    }

    /**
     * Perform one step over progress bar.
     * @param string $info
     * @param int $step
     * @return $this
     */
    public function step(string $info = '', int $step = 1): self
    {
        $this->progress += $step;

        if ($this->progress > $this->total) {
            $this->progress = $this->total;
            $percentage = 100;
        } elseif ($this->progress < 0) {
            $this->progress = 0;
            $percentage = 0;
        } else {
            $percentage = (int) round(($this->progress * 100) / $this->total);
        }

        $this->writeProgress($percentage, $info);

        return $this;
    }

    /**
     * Set progress percentage directly.
     * @param int $percentage
     * @param string $info
     * @return $this
     */
    public function setPercentage(int $percentage, string $info = ''): self
    {
        if ($percentage > 100) {
            $percentage = 100;
        } elseif ($percentage < 0) {
            $percentage = 0;
        }
        $this->progress = (int) round($percentage / 100 * $this->total);

        $this->writeProgress($percentage, $info);

        return $this;
    }

    /**
     * Finalize progress bar.
     * @param string $message
     * @return $this
     */
    public function finish($message = ''): self
    {
        $this->write('', true);

        if ($message) {
            $this->write($message, true);
        }

        return $this;
    }

    /**
     * Output the progress bar.
     * @param int $percentage
     * @param string $info
     * @return $this
     */
    private function writeProgress(int $percentage, string $info = ''): self
    {
        $bar = (int) round(($this->width * $percentage) / 100);

        return $this->write(
            str_pad($percentage . '%', 4)
            . '[' . str_repeat('=', $bar) . '>' . str_repeat(' ', $this->width - $bar) . ']'
            . ($info ? ' ' . $info : '') . "\r"
        );
    }

    /**
     * Output the text.
     * @param string $text
     * @param bool $newLine
     * @return $this
     */
    private function write(string $text = '', bool $newLine = false): self
    {
        if (!$this->mute) {
            echo $text . ($newLine ? "\n" : '');
        }

        return $this;
    }
}
