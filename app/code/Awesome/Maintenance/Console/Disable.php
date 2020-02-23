<?php

namespace Awesome\Maintenance\Console;

use Awesome\Maintenance\Model\Maintenance;

class Disable extends \Awesome\Framework\Model\Cli\AbstractCommand
{
    /**
     * @var Maintenance $maintenance
     */
    private $maintenance;

    /**
     * Maintenance Disable constructor.
     */
    public function __construct()
    {
        $this->maintenance = new Maintenance();
    }

    /**
     * Disable maintenance mode.
     * @inheritDoc
     */
    public function execute($input, $output)
    {
        $this->maintenance->disable();

        $output->writeln('Maintenance mode was disabled.');
    }
}
