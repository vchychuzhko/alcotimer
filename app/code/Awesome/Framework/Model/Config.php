<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\FileManager\PhpFileManager;

class Config
{
    private const CONFIG_FILE_PATH = '/app/etc/config.php';
    private const CONFIG_ANNOTATION = 'General configuration file';

    private PhpFileManager $phpFileManager;

    private array $config;

    /**
     * Config constructor.
     * @param PhpFileManager $phpFileManager
     */
    public function __construct(
        PhpFileManager $phpFileManager
    ) {
        $this->phpFileManager = $phpFileManager;
    }

    /**
     * Get config value by path.
     * Method consider the path as chain of keys: a/b/c => ['a']['b']['c']
     * @param string $path
     * @return mixed
     */
    public function get(string $path)
    {
        $keys = explode('/', $path);
        $config = $this->getConfig();

        foreach ($keys as $key) {
            if (is_array($config) && isset($config[$key])) {
                $config = $config[$key];
            } else {
                return null;
            }
        }

        return $config;
    }

    /**
     * Set config value by path, creating a record if not yet.
     * Method consider the path as chain of keys: a/b/c => ['a']['b']['c']
     * @param string $path
     * @param mixed $value
     * @return bool
     */
    public function set(string $path, $value): bool
    {
        $success = false;

        if ($path) {
            $keys = array_reverse(explode('/', $path));
            $newConfig = $value;

            foreach ($keys as $key) {
                $newConfig = [
                    $key => $newConfig,
                ];
            }
            $config = array_replace_recursive($this->getConfig(), $newConfig);

            $success = $this->phpFileManager->createArrayFile(BP . self::CONFIG_FILE_PATH, $config, self::CONFIG_ANNOTATION);
            $this->config = $config;
        }

        return $success;
    }

    /**
     * Load and return config by including configuration file.
     * @return array
     */
    private function getConfig(): array
    {
        if (!isset($this->config)) {
            $this->config = $this->phpFileManager->parseArrayFile(BP . self::CONFIG_FILE_PATH);
        }

        return $this->config;
    }
}
