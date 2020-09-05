<?php

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
    public function get($path)
    {
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

        return $config;
    }

    /**
     * Set config value by path.
     * Method consider the path as chain of keys: a/b/c => ['a']['b']['c']
     * @param string $path
     * @param mixed $value
     * @return bool
     */
    public function set($path, $value)
    {
        $success = false;

        if ($this->get($path) !== null) {
            $newConfig = $value;
            $config = $this->getConfig();
            $keys = array_reverse(explode('/', $path));

            foreach ($keys as $key) {
                $newConfig = [
                    $key => $newConfig
                ];
            }
            $config = array_replace_recursive($config, $newConfig);

            $this->phpFileManager->createArrayFile(BP . self::CONFIG_FILE_PATH, $config, self::CONFIG_ANNOTATION);
            $success = true;
        }

        return $success;
    }

    /**
     * Load and get config by including configuration file.
     * Can be forced to reload the file.
     * @param bool $reload
     * @return array
     */
    private function getConfig($reload = false)
    {
        if ($this->config === null || $reload) {
            $this->config = $this->phpFileManager->includeFile(BP . self::CONFIG_FILE_PATH);
        }

        return $this->config;
    }
}
