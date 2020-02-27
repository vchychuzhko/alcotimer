<?php

namespace Awesome\Maintenance\Console;

use Awesome\Maintenance\Model\Maintenance;

class Status extends \Awesome\Framework\Model\Cli\AbstractCommand
{
    /**
     * @var Maintenance $maintenance
     */
    private $maintenance;

    /**
     * Maintenance Status constructor.
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
            ->setDescription('View current state of maintenance.');
    }

    /**
     * Get current state of maintenance.
     * @inheritDoc
     */
    public function execute($input, $output)
    {
        $status = 'Maintenance mode is disabled.';
        $state = $this->maintenance->getStatus();

        if ($state['enabled']) {
            $allowedIPs = implode(', ', $state['allowed_ips'] ?? []);
            $status = 'Maintenance mode is enabled.'
                . ($allowedIPs ? ("\n" . 'Allowed IP addresses: ' . $output->colourText($allowedIPs, 'brown')) : '');
        }

        $output->writeln($status);
    }
}
