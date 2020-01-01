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
     * Config constructor.
     */
    public function __construct()
    {
        $this->config = $this->loadConfig();
        //@TODO: add setConfig() functionality
    }

    /**
     * Include configuration file.
     * @return array
     */
    private function loadConfig()
    {
        return include(BP . self::CONFIG_FILE_PATH);
    }

    /**
     * Get config value by path.
     * @param string $path
     * @return mixed
     */
    public function getConfig($path)
    {
        $config = null;

        if ($pathParts = $this->checkPath($path)) {
            $config = $this->config;

            foreach($pathParts as $pathPart) {
                $config = $config[$pathPart];
            }
        }

        return $config;
    }

    /**
     * Check if requested path exists and return its exploded path.
     * Returns empty array if path does not exist.
     * @param string $path
     * @return array
     */
    private function checkPath($path)
    {
        $pathParts = explode(self::CONFIG_PATH_DELIMITER, $path);
        $exists = false;

        $config = $this->config;

        foreach($pathParts as $pathPart) {
            if ($exists = isset($config[$pathPart])) {
                $config = $config[$pathPart];
            } else {
                break;
            }
        }

        return $exists ? $pathParts : [];
    }
}
