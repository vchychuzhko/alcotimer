<?php

namespace Awesome\Framework\Model;

class Config implements \Awesome\Framework\Model\SingletonInterface
{
    private const CONFIG_FILE_PATH = '/app/etc/config.php';

    /**
     * @var array $config
     */
    private $config;

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
     * @param string $path
     * @param mixed $value
     * @return $this
     */
    public function set($path, $value)
    {
        //@TODO: Implement config set functionality

        return $this;
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
            $this->config = include BP . self::CONFIG_FILE_PATH;
        }

        return $this->config;
    }
}
