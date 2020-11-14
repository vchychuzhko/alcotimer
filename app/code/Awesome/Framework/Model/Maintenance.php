<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

class Maintenance
{
    private const MAINTENANCE_FILE = '/var/maintenance.flag';

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * Maintenance constructor.
     * @param FileManager $fileManager
     */
    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * Enable maintenance mode.
     * @param array $allowedIPs
     * @return $this
     */
    public function enable($allowedIPs = []): self
    {
        $this->fileManager->createFile(BP . self::MAINTENANCE_FILE, implode(',', $allowedIPs), true);

        return $this;
    }

    /**
     * Disable maintenance mode.
     * @return $this
     */
    public function disable(): self
    {
        $this->fileManager->removeFile(BP . self::MAINTENANCE_FILE);

        return $this;
    }

    /**
     * Get current state of maintenance.
     * @return array
     */
    public function getStatus(): array
    {
        $status = [
            'enabled' => false
        ];

        if (($allowedIPs = $this->fileManager->readFile(BP . self::MAINTENANCE_FILE)) !== false) {
            $status = [
                'enabled' => true,
                'allowed_ips' => []
            ];

            if ($allowedIPs) {
                $status['allowed_ips'] = explode(',', $allowedIPs);
            }
        }

        return $status;
    }

    /**
     * Check if maintenance mode is currently enabled.
     * User IP address can be specified.
     * @param string $ip
     * @return bool
     */
    public function isMaintenance(string $ip = ''): bool
    {
        $state = $this->getStatus();

        return $state['enabled'] && !in_array($ip, $state['allowed_ips'], true);
    }
}
