<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\FileManager\PhpFileManager;

class Config implements \Awesome\Framework\Model\SingletonInterface
{
    private const CONFIG_FILE_PATH = '/app/etc/config.php';
    private const CONFIG_ANNOTATION = 'General configuration file';

    /**
     * @var PhpFileManager $phpFileManager
     */
    private $phpFileManager;

    /**
     * @var array $config
     */
    private $config;

    /**
     * Config constructor.
     * @param PhpFileManager $phpFileManager
     */
    public function __construct(PhpFileManager $phpFileManager)
    {
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
        $config = null;

        if ($path) {
            $keys = explode('/', $path);
            $config = $this->getConfig();

            foreach ($keys as $key) {
                if (is_array($config) && isset($config[$key])) {
                    $config = $config[$key];
                } else {
                    $config = null;
                    break;
                }
            }
        }

        return $config;
    }

    /**
     * Check if provided config record exists.
     * Method consider the path as chain of keys: a/b/c => ['a']['b']['c']
     * @param string $path
     * @return mixed
     */
    public function exists(string $path): bool
    {
        $exists = false;

        if ($path) {
            $keys = explode('/', $path);
            $config = $this->getConfig();

            foreach ($keys as $key) {
                if (is_array($config) && array_key_exists($key, $config)) {
                    $config = $config[$key];
                    $exists = true;
                } else {
                    $exists = false;
                    break;
                }
            }
        }

        return $exists;
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
        if ($this->config === null) {
            $this->config = $this->phpFileManager->parseArrayFile(BP . self::CONFIG_FILE_PATH);
        }

        return $this->config;
    }
}
