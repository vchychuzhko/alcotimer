<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

class Maintenance implements \Awesome\Framework\Model\SingletonInterface
{
    private const MAINTENANCE_FILE = '/var/maintenance.flag';

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * @var array $status
     */
    private $status;

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
     * @param array $allowedIps
     * @return $this
     */
    public function enable($allowedIps = []): self
    {
        $this->fileManager->createFile(BP . self::MAINTENANCE_FILE, implode("\n", $allowedIps), true);

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
     * Check if maintenance mode is currently active.
     * User IP address can be specified.
     * @param string $ip
     * @return bool
     */
    public function isActive(string $ip = ''): bool
    {
        $status = $this->getStatus();

        return $status['active'] && !($ip && in_array($ip, $this->getAllowedIps(), true));
    }

    /**
     * Get current state of maintenance.
     * @return array
     */
    public function getAllowedIps(): array
    {
        $status = $this->getStatus();

        return $status['allowed_ips'];
    }

    /**
     * Load and return maintenance status by reading flag file.
     * @return array
     */
    private function getStatus(): array
    {
        if ($this->status === null) {
            $this->status = [
                'active'      => false,
                'allowed_ips' => [],
            ];
            $allowedIps = $this->fileManager->readFile(BP . self::MAINTENANCE_FILE, true);

            if ($allowedIps !== false) {
                $this->status = [
                    'active'      => true,
                    'allowed_ips' => $allowedIps ? explode("\n", $allowedIps) : [],
                ];
            }
        }

        return $this->status;
    }
}
