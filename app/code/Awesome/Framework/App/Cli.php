<?php

namespace Awesome\Framework\App;

use Awesome\Framework\Console\Help;
use Awesome\Framework\Exception\NoSuchEntityException;
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
     * @var Help $help
     */
    private $help;

    /**
     * @var Output $output
     */
    private $output;

    /**
     * @var Input $input
     */
    private $input;

    /**
     * Console app constructor.
     */
    public function __construct()
    {
        $this->cliHandler = new CliHandler();
        $this->help = new Help();
        $this->output = new Output();
    }

    /**
     * Run the CLI application.
     * @inheritDoc
     */
    public function run()
    {
        try {
            $this->input = $this->cliHandler->parseInput();
            $command = $this->input->getCommand();

            if ($this->isQuiet()) {
                $this->output->mute();
            }

            if ($this->isNonInteractive()) {
                $this->input->disableInteraction();
            }

            if ($this->showVersion()) {
                $this->showAppCliTitle();
            } elseif ($this->showCommandHelp()) {
                $this->help->execute($this->input, $this->output);
            } elseif ($command && $className = $this->cliHandler->process($command)) {
                /** @var AbstractCommand $consoleClass */
                $consoleClass = new $className();
                $consoleClass->execute($this->input, $this->output);
            } else {
                $this->showAppCliTitle();
                $this->output->writeln();
                $this->help->execute($this->input, $this->output);
            }
        } catch (NoSuchEntityException $e) {
            $this->output->writeln($this->output->colourText($e->getMessage(), Output::WHITE, Output::RED_BG));

            if ($candidates = $this->cliHandler->getPossibleCandidates($e->getEntity(), false)) {
                $this->output->writeln();
                $this->output->writeln('Did you mean one of these?', 2);

                foreach ($candidates as $candidate) {
                    $this->output->writeln($this->output->colourText($candidate, Output::BROWN), 4);
                }
            } else {
                $this->output->writeln(sprintf('Try running help, using only "%s" option.', '-h'));
            }

            throw $e;
        } catch (\LogicException $e) {
            $this->output->writeln($this->output->colourText($e->getMessage(), Output::WHITE, Output::RED_BG));

            throw $e;
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
     * Determine if command help should be shown.
     * @return bool
     */
    private function showCommandHelp()
    {
        return $this->input->getOption('help') && ($this->input->getCommand() || $this->input->getArgument('command'));
    }

    /**
     * Output application CLI title with version.
     */
    private function showAppCliTitle()
    {
        $this->output->writeln('AlcoTimer CLI ' . $this->output->colourText(self::VERSION));
    }
}
