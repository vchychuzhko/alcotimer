<?php

namespace Awesome\Base\Model;

class Config
{
    private const CONFIG_FILE_PATH = '/app/etc/config.php';
    private const PATH_DELIMITER = '/';

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
    }

    /**
     * Include configuration file.
     * @return array
     */
    private function loadConfig()
    {
        $config = @include(BP . self::CONFIG_FILE_PATH);

        return $config ?: [];
    }

    /**
     * Get config value by path.
     * @param string $path
     * @return string
     */
    public function getConfig($path)
    {
        $config = '';

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
        $pathParts = explode(self::PATH_DELIMITER, $path);
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
