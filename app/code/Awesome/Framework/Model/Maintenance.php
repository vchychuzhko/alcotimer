<?php

namespace Awesome\Framework\Model;

class Maintenance
{
    public const MAINTENANCE_FILE = '/var/maintenance.flag';
    public const MAINTENANCE_PAGE_PATH = '/pub/pages/maintenance.html';

    /**
     * Enable maintenance mode.
     * @param array $allowedIPs
     * @return $this
     */
    public function enable($allowedIPs = [])
    {
        file_put_contents(BP . self::MAINTENANCE_FILE, implode(',', $allowedIPs));

        return $this;
    }

    /**
     * Disable maintenance mode.
     * @return $this
     */
    public function disable()
    {
        @unlink(BP . self::MAINTENANCE_FILE);

        return $this;
    }

    /**
     * Get current state of maintenance.
     * @return array
     */
    public function getStatus()
    {
        $status = [
            'enabled' => false
        ];

        if (($allowedIPs = @file_get_contents(BP . self::MAINTENANCE_FILE)) !== false) {
            $status = [
                'enabled' => true,
                'allowed_ips' => []
            ];

            if ($allowedIPs) {
                $status['allowed_ips'] = explode(',', $allowedIPs);
            }
        };

        return $status;
    }

    /**
     * Check if maintenance mode is currently enabled.
     * IP address can be specified.
     * @param string $ip
     * @return bool
     */
    public function isMaintenance($ip = '') {
        $state = $this->getStatus();

        return $state['enabled'] && !in_array($ip, $state['allowed_ips']);
    }

    /**
     * Get maintenance page.
     * @return string
     */
    public function getMaintenancePage()
    {
        return file_get_contents(BP . Maintenance::MAINTENANCE_PAGE_PATH);
    }
}
