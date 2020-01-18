<?php

namespace Awesome\Framework\Model;

class Config
{
    private const CONFIG_FILE_PATH = '/app/etc/config.php';
    private const CONFIG_PATH_DELIMITER = '/';

    /**
     * @var array $config
     */
    private $config;

    /**
     * Get config value by path.
     * Default value will be returned if no corresponding config is found.
     * @param string $path
     * @param mixed|null $defaultValue
     * @return mixed
     */
    public function get($path, $defaultValue = null)
    {
        $config = $defaultValue;

        if ($this->pathExist($path)) {
            $pathParts = $this->parseConfigPath($path);
            $config = $this->getConfig();

            foreach($pathParts as $pathPart) {
                $config = $config[$pathPart];
            }
        }

        return $config;
    }

    /**
     * Set config value by path.
     * @param string $key
     * @param mixed|null $value
     * @return $this
     */
    public function set($key, $value)
    {
        //@TODO: Implement config set functionality

        return $this;
    }

    /**
     * Check if the requested config path exists.
     * @param string $path
     * @return bool
     */
    private function pathExist($path)
    {
        $pathParts = $this->parseConfigPath($path);
        $config = $this->getConfig();
        $exists = false;

        foreach($pathParts as $pathPart) {
            if ($exists = isset($config[$pathPart])) {
                $config = $config[$pathPart];
            } else {
                break;
            }
        }

        return $exists;
    }

    /**
     * Parse requested path string into array of path parts.
     * @param string $path
     * @return array
     */
    private function parseConfigPath($path)
    {
        return explode(self::CONFIG_PATH_DELIMITER, $path);
    }

    /**
     * Load and get config by including configuration file.
     * Can be forced to re-parse the file.
     * @param bool $forceUpdate
     * @return array
     */
    private function getConfig($forceUpdate = false)
    {
        if ($this->config === null || $forceUpdate) {
            $this->config = include(BP . self::CONFIG_FILE_PATH);
        }

        return $this->config;
    }
}
