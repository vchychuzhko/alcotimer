<?php
declare(strict_types=1);

namespace Vch\Framework\Console;

use Vch\Console\Model\Cli\Input;
use Vch\Console\Model\Cli\Input\InputDefinition;
use Vch\Console\Model\Cli\Output;
use Vch\Framework\Model\Maintenance;

class MaintenanceDisable extends \Vch\Console\Model\AbstractCommand
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
    public static function configure(): InputDefinition
    {
        return parent::configure()
            ->setDescription('Disable maintenance mode');
    }

    /**
     * Disable maintenance mode.
     * @inheritDoc
     */
    public function execute(Input $input, Output $output)
    {
        $this->maintenance->disable();

        $output->writeln('Maintenance mode has been disabled.');
    }
}
