<?php
declare(strict_types=1);

namespace Vch\Frontend\Model;

use Vch\Framework\Model\FileManager;
use Vch\Frontend\Model\StaticContent;

class DeployedVersion
{
    private const DEPLOYED_VERSION_FILE = 'deployed_version.txt';

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * @var int $deployedVersion
     */
    private $deployedVersion;

    /**
     * DeployedVersion constructor.
     * @param FileManager $fileManager
     */
    public function __construct(
        FileManager $fileManager
    ) {
        $this->fileManager = $fileManager;
    }

    /**
     * Generate static deployed version and save it.
     * @return $this
     */
    public function generateVersion(): self
    {
        $this->deployedVersion = time();
        $this->fileManager->createFile(
            BP . StaticContent::STATIC_FOLDER_PATH . self::DEPLOYED_VERSION_FILE,
            (string) $this->deployedVersion,
            true
        );

        return $this;
    }

    /**
     * Get current static deployed version.
     * @return int|null
     */
    public function getVersion(): ?int
    {
        if ($this->deployedVersion === null) {
            $deployedVersion = $this->fileManager->readFile(BP . StaticContent::STATIC_FOLDER_PATH . self::DEPLOYED_VERSION_FILE, true);

            if ($deployedVersion) {
                $this->deployedVersion = (int) $deployedVersion;
            }
        }

        return $this->deployedVersion;
    }
}
