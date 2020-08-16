<?php

namespace Awesome\Framework\Console;

use Awesome\Framework\Model\Maintenance;

class MaintenanceDisable extends \Awesome\Console\Model\Cli\AbstractCommand
{
    /**
     * @var Maintenance $maintenance
     */
    private $maintenance;

    /**
     * Maintenance Disable constructor.
     * @param Maintenance $maintenance
     */
    public function __construct(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
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
