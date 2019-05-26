<?php

namespace Ava\Console\Command;

class Maintenance extends \Ava\Console\AbstractCommand
{
    /**
     * Enable maintenance mode.
     * @param array $args
     * @return string
     */
    public function enable($args = [])
    {
        if ($args) {
            $allowedIPs = $this->parseArguments($args)['ip'] ?? [];
        }

        return 'Maintenance mode was enabled.';
    }

    /**
     * Disable maintenance mode.
     * @return string
     */
    public function disable()
    {
        return 'Maintenance mode was disabled.';
    }

    /**
     * Get current state of maintenance.
     * @return string
     */
    public function status()
    {
        return 'status.';
    }
}
