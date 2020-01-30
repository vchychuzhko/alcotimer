<?php

namespace Awesome\Maintenance\Console;

class Status extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * @var \Awesome\Maintenance\Model\Maintenance $maintenance
     */
    private $maintenance;

    /**
     * Maintenance Status constructor.
     * @inheritDoc
     */
    public function __construct($options = [], $arguments = [])
    {
        $this->maintenance = new \Awesome\Maintenance\Model\Maintenance();
        parent::__construct($options, $arguments);
    }

    /**
     * Get current state of maintenance.
     * @inheritDoc
     */
    public function execute($output)
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
