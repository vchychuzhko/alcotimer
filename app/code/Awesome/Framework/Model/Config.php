<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\FileManager\PhpFileManager;

class Config implements \Awesome\Framework\Model\SingletonInterface
{
    private const CONFIG_FILE_PATH = '/app/etc/config.php';
    private const CONFIG_ANNOTATION = 'General configuration file';

    /**
     * @var array $config
     */
    private $config;

    /**
     * @var PhpFileManager $phpFileManager
     */
    private $phpFileManager;

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
        $keys = explode('/', $path);
        $config = $this->loadConfig();

        foreach ($keys as $key) {
            if (is_array($config) && isset($config[$key])) {
                $config = $config[$key];
            } else {
                $config = null;
                break;
            }
        }

        return $config;
    }

    /**
     * Check if provided config path exists.
     * Method consider the path as chain of keys: a/b/c => ['a']['b']['c']
     * @param string $path
     * @return mixed
     */
    private function exists(string $path): bool
    {
        $exists = false;
        $keys = explode('/', $path);
        $config = $this->loadConfig();

        foreach ($keys as $key) {
            if (is_array($config) && isset($config[$key])) {
                $config = $config[$key];
                $exists = true;
            } else {
                $exists = false;
                break;
            }
        }

        return $exists;
    }

    /**
     * Set config value by path.
     * Method consider the path as chain of keys: a/b/c => ['a']['b']['c']
     * @param string $path
     * @param mixed $value
     * @return bool
     */
    public function set(string $path, $value): bool
    {
        $success = false;

        if ($this->exists($path) !== null) {
            $config = $this->loadConfig();
            $keys = array_reverse(explode('/', $path));
            $newConfig = $value;

            foreach ($keys as $key) {
                $newConfig = [
                    $key => $newConfig
                ];
            }
            $config = array_replace_recursive($config, $newConfig);

            $success = $this->phpFileManager->createArrayFile(BP . self::CONFIG_FILE_PATH, $config, self::CONFIG_ANNOTATION);
            $this->loadConfig(true);
        }

        return $success;
    }

    /**
     * Load and return config by including configuration file.
     * Can be forced to reload the file.
     * @param bool $reload
     * @return array
     */
    private function loadConfig(bool $reload = false): array
    {
        if ($this->config === null || $reload) {
            $this->config = $this->phpFileManager->readArrayFile(BP . self::CONFIG_FILE_PATH, true);
        }

        return $this->config;
    }
}
