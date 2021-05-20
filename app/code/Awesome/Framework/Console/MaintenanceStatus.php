<?php
declare(strict_types=1);

namespace Awesome\Framework\Console;

use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Framework\Model\Maintenance;

class MaintenanceStatus extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * @var Maintenance $maintenance
     */
    private $maintenance;

    /**
     * Maintenance Status constructor.
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
            ->setDescription('Show maintenance mode status');
    }

    /**
     * Get current state of maintenance.
     * @inheritDoc
     */
    public function execute(Input $input, Output $output): void
    {
        $output->writeln($this->maintenance->isActive() ? 'Maintenance mode is active.' : 'Maintenance mode is disabled.');

        if ($allowedIps = $this->maintenance->getAllowedIps()) {
            $output->writeln('Allowed IP addresses: ' . $output->colourText(implode(', ', $allowedIps), Output::BROWN));
        }
    }
}
