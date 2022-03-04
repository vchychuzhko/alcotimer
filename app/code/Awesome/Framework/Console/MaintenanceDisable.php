<?php
declare(strict_types=1);

namespace Awesome\Framework\Console;

use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Framework\Model\Maintenance;

class MaintenanceDisable extends \Awesome\Console\Model\AbstractCommand
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
