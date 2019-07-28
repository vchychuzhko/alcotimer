<?php

namespace Awesome\Cache\Model;

class StaticContent
{
    private const DEPLOYED_VERSION_FILE = '/pub/static/deployed_version.txt';
    private const ASSET_PUB_TRIGGER = '{@pubDir}';
    private const ASSET_PUB_REPLACE = '../../../..';
    private const FRONTEND_STATIC_PATH = '/pub/static/frontend';
    private const CSS_PATH_PATTERN = '/assets/css/*.css';

    /**
     * Deploy static files.
     * @return self
     */
    function deploy() {
        $this->removeStatic();
        $this->processCss();
        $this->generateDeployedVersion();

        return $this;
    }

    /**
     * Remove all static files and recreate frontend directory.
     * @return self
     */
    public function removeStatic() {
        rrmdir(BP . self::FRONTEND_STATIC_PATH);
        mkdir(BP . self::FRONTEND_STATIC_PATH);

        return $this;
    }

    /**
     * Collect, parse and generate css files.
     * @return self
     */
    private function processCss() {
        $cssDir = BP . self::FRONTEND_STATIC_PATH . '/css';

        if (!file_exists($cssDir)) {
            mkdir($cssDir);
        }

        foreach (glob(BP . self::CSS_PATH_PATTERN) as $cssFile) {
            $fileName = $this->getFileName($cssFile);
            $content = file_get_contents($cssFile);
            $content = $this->parsePubDirPath($content);
            file_put_contents($cssDir . '/' . $fileName, $content);
        }

        return $this;
    }

    /**
     * Retrieve file name by the whole path.
     * @param string $filePath
     * @return string
     */
    private function getFileName($filePath) {
        $filePath = explode('/', $filePath);

        return end($filePath);
    }

    /**
     * Replace pub dir placeholder with the correct path.
     * @param string $content
     * @return string
     */
    private function parsePubDirPath($content) {
        return str_replace(self::ASSET_PUB_TRIGGER, self::ASSET_PUB_REPLACE, $content);
    }

    /**
     * Generate new deployed version and save it.
     * @return self
     */
    public function generateDeployedVersion() {
        file_put_contents(BP . self::DEPLOYED_VERSION_FILE, time());

        return $this;
    }

    /**
     * Get current static deployed version.
     * @return string
     */
    public function getDeployedVersion()
    {
        $version = @file_get_contents(BP . self::DEPLOYED_VERSION_FILE);

        return (string)$version;
    }
}
