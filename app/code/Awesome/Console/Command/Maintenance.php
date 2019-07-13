<?php

namespace Awesome\Console\Command;

class Maintenance extends \Awesome\Console\AbstractCommand
{
    public const MAINTENANCE_FILE = 'maintenance.flag';

    /**
     * Enable maintenance mode.
     * @param array $args
     * @return string
     */
    public function enable($args = [])
    {
        $allowedIPs = $this->parseArguments($args)['ip'] ?? [];
        file_put_contents(BP . DS . self::MAINTENANCE_FILE, implode(',', $allowedIPs));

        return $this->colourText('Maintenance mode was enabled.');
    }

    /**
     * Disable maintenance mode.
     * @return string
     */
    public function disable()
    {
        @unlink(BP . DS . self::MAINTENANCE_FILE);

        return $this->colourText('Maintenance mode was disabled.');
    }

    /**
     * Get current state of maintenance.
     * @return string
     */
    public function status()
    {
        $status = 'Maintenance mode is disabled.';

        if (($allowedIPs = @file_get_contents(BP . DS . self::MAINTENANCE_FILE)) !== false) {
            $allowedIPs = str_replace(',', ', ', $allowedIPs);
            $status = 'Maintenance mode is enabled.'
                . ($allowedIPs ? ("\n" . 'List of allowed ip addresses: ' . $allowedIPs) : '');
        };

        return $status;
    }
}
