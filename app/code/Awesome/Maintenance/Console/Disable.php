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
     * @inheritDoc
     */
    public static function configure($definition)
    {
        return parent::configure($definition)
            ->setDescription('Disable maintenance mode');
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
