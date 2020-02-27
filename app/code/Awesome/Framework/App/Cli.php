<?php

namespace Awesome\Framework\App;

use Awesome\Framework\Console\ShowHelp;
use Awesome\Framework\Handler\CliHandler;
use Awesome\Framework\Model\Cli\AbstractCommand;
use Awesome\Framework\Model\Cli\Input;
use Awesome\Framework\Model\Cli\Output;

class Cli implements \Awesome\Framework\Model\AppInterface
{
    /**
     * @var CliHandler $cliHandler
     */
    private $cliHandler;

    /**
     * @var Input $input
     */
    private $input;

    /**
     * @var Output $output
     */
    private $output;

    /**
     * Console app constructor.
     */
    public function __construct()
    {
        $this->cliHandler = new CliHandler();
        $this->output = new Output();
    }

    /**
     * Run the CLI application.
     * @inheritDoc
     */
    public function run()
    {
        $this->input = $this->cliHandler->parseInput();

        if ($this->isQuiet()) {
            $this->output->mute();
        }

        if ($this->isNonInteractive()) {
            $this->input->disableInteraction();
        }

        if ($this->showVersion()) {
            $this->showAppCliTitle();
        } elseif ($command = $this->input->getCommand()) {
            if ($className = $this->cliHandler->process($command)) {
                /** @var AbstractCommand $consoleClass */
                $consoleClass = new $className();
                $consoleClass->execute($this->input, $this->output);
            } else {
                $candidates = $this->cliHandler->getPossibleCandidates($command, false);
                $this->output->writeln(
                    $this->output->colourText('Command "' . $command . '" is not defined.', 'white', 'red')
                );

                if ($candidates) {
                    $this->output->writeln();
                    $this->output->writeln('Did you mean one of these?', 2);

                    foreach ($candidates as $candidate) {
                        $this->output->writeln($this->output->colourText($candidate, 'brown'), 4);
                    }
                }
            }
        } else {
            $this->showAppCliTitle();
            $this->output->writeln();

            $help = new ShowHelp();
            $help->execute($this->input, $this->output);
        }
    }

    /**
     * Determine if output should be disabled.
     * @return bool
     */
    private function isQuiet()
    {
        return $this->input->getOption('quiet');
    }

    /**
     * Determine if user interaction should be disabled.
     * @return bool
     */
    private function isNonInteractive()
    {
        return $this->input->getOption('no-interaction');
    }

    /**
     * Determine if application version should be shown.
     * @return bool
     */
    private function showVersion()
    {
        return $this->input->getOption('version');
    }

    /**
     * Determine if help should be shown.
     * @return bool
     */
    private function showHelp()
    {
        return $this->input->getOption('help');
    }

    /**
     * Output application CLI title with version.
     */
    private function showAppCliTitle()
    {
        $this->output->writeln('AlcoTimer CLI ' . $this->output->colourText(self::VERSION));
    }
}
