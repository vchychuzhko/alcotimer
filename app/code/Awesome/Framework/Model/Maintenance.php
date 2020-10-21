<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

class Maintenance
{
    public const MAINTENANCE_FILE = '/var/maintenance.flag';

    public const MAINTENANCE_PAGE_PATH = '/pub/pages/maintenance.html';
    public const INTERNALERROR_PAGE_PATH = '/pub/pages/internal_error.html';

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

    /**
     * Get maintenance page.
     * @return string
     */
    public function getMaintenancePage(): string
    {
        return $this->fileManager->readFile(BP . self::MAINTENANCE_PAGE_PATH, false);
    }

    /**
     * Get internal error page.
     * @return string
     */
    public function getInternalErrorPage(): string
    {
        return $this->fileManager->readFile(BP . self::INTERNALERROR_PAGE_PATH, false);
    }
}
