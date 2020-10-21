<?php
declare(strict_types=1);

namespace Awesome\Framework\Console;

use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Framework\Model\Maintenance;

class MaintenanceStatus extends \Awesome\Console\Model\Cli\AbstractCommand
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
    public static function configure(InputDefinition $definition): InputDefinition
    {
        return parent::configure($definition)
            ->setDescription('View current state of maintenance');
    }

    /**
     * Get current state of maintenance.
     * @inheritDoc
     */
    public function execute(Input $input, Output $output): void
    {
        $status = 'Maintenance mode is disabled.';
        $state = $this->maintenance->getStatus();

        if ($state['enabled']) {
            $status = 'Maintenance mode is enabled.';

            if ($state['allowed_ips']) {
                $allowedIPs = implode(', ', $state['allowed_ips']);
                $status .= "\n" . 'Allowed IP addresses: ' . $output->colourText($allowedIPs, Output::BROWN);
            }
        }

        $output->writeln($status);
    }
}
