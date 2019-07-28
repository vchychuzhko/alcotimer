<?php

namespace Awesome\Maintenance\Console;

use Awesome\Maintenance\Model\Maintenance;

class Disable extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * @var Maintenance $maintenance
     */
    private $maintenance;

    /**
     * Disable constructor.
     */
    public function __construct()
    {
        $this->maintenance = new Maintenance();
    }

    /**
     * Disable maintenance mode.
     * @inheritDoc
     */
    public function execute($args = [])
    {
        $this->maintenance->disable();

        return $this->colourText('Maintenance mode was disabled.');
    }
}
