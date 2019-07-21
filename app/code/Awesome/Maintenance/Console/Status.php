<?php

namespace Awesome\Maintenance\Console;

use Awesome\Maintenance\Model\Maintenance;

class Status extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * @var Maintenance $maintenance
     */
    private $maintenance;

    /**
     * Status constructor.
     */
    public function __construct()
    {
        $this->maintenance = new Maintenance();
    }

    /**
     * Get current state of maintenance.
     * @inheritDoc
     */
    public function execute($args = [])
    {
        $status = 'Maintenance mode is disabled.';
        $state = $this->maintenance->getStatus();

        if ($state['enabled']) {
            $allowedIPs = implode(', ', $state['allowed_ips'] ?? []);
            $status = 'Maintenance mode is enabled.'
                . ($allowedIPs ? ("\n" . 'List of allowed ip addresses: ' . $allowedIPs) : '');
        }

        return $status;
    }
}
