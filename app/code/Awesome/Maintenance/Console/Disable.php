<?php

namespace Awesome\Maintenance\Console;

class Disable extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * @var \Awesome\Maintenance\Model\Maintenance $maintenance
     */
    private $maintenance;

    /**
     * Maintenance Disable constructor.
     * @inheritDoc
     */
    public function __construct($options = [], $arguments = [])
    {
        $this->maintenance = new \Awesome\Maintenance\Model\Maintenance();
        parent::__construct($options, $arguments);
    }

    /**
     * Disable maintenance mode.
     * @inheritDoc
     */
    public function execute()
    {
        $this->maintenance->disable();

        return $this->colourText('Maintenance mode was disabled.');
    }
}
