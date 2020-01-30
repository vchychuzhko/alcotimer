<?php

namespace Awesome\Maintenance\Console;

class Enable extends \Awesome\Framework\Model\Cli\AbstractCommand
{
    /**
     * @var \Awesome\Maintenance\Model\Maintenance $maintenance
     */
    private $maintenance;

    /**
     * Maintenance Enable constructor.
     * @inheritDoc
     */
    public function __construct($options = [], $arguments = [])
    {
        $this->maintenance = new \Awesome\Maintenance\Model\Maintenance();
        parent::__construct($options, $arguments);
    }

    /**
     * Enable maintenance mode.
     * @inheritDoc
     */
    public function execute($output)
    {
        $allowedIPs = $this->options['ip'] ?? [];
        $this->maintenance->enable($allowedIPs);
        //@TODO: add IP address validation

        $output->writeln('Maintenance mode was enabled.');
    }
}
