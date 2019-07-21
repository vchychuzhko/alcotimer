<?php

namespace Awesome\Maintenance\Console;

use \Awesome\Maintenance\Model\Maintenance;

class Enable extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * @var Maintenance $maintenance
     */
    private $maintenance;

    /**
     * Enable constructor.
     */
    public function __construct()
    {
        $this->maintenance = new Maintenance();
    }

    /**
     * Enable maintenance mode.
     * @inheritDoc
     */
    public function execute($args = [])
    {
        $allowedIPs = $this->parseArguments($args)['ip'] ?? [];
        $this->maintenance->enable($allowedIPs);

        return $this->colourText('Maintenance mode was enabled.');
    }
}
