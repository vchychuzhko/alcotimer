<?php

namespace Awesome\Cache\Model;

class StaticContent
{
    private const DEPLOYED_VERSION_FILE = '/pub/static/deployed_version.txt';
    private const ASSET_PUB_TRIGGER = '{@pubDir}';
    private const ASSET_PUB_REPLACE = '../../../..';
    private const FRONTEND_STATIC_PATH = '/pub/static/frontend';
    private const CSS_PATH_PATTERN = '/assets/css/*.css';
    private const JS_PATH_PATTERN = '/assets/js/*.js';
    private const JS_LIB_PATH_PATTERN = '/lib/*/*.js';

    /**
     * Deploy static files.
     * @return self
     */
    function deploy()
    {
        $this->removeStatic();
        $this->generateAssets();
        $this->processLibs();
        $this->generateDeployedVersion();

        return $this;
    }

    /**
     * Remove all static files and recreate frontend directory.
     * @return self
     */
    public function removeStatic()
    {
        rrmdir(BP . self::FRONTEND_STATIC_PATH);
        mkdir(BP . self::FRONTEND_STATIC_PATH);

        return $this;
    }

    /**
     * Collect, parse and generate css/js files.
     * @return self
     */
    private function generateAssets()
    {
        $assets = [
            'css' => BP . self::CSS_PATH_PATTERN,
            'js' => BP . self::JS_PATH_PATTERN
        ];

        foreach ($assets as $asset => $assetPattern) {
            $assetFolder = BP . self::FRONTEND_STATIC_PATH . '/' . $asset;

            if (!file_exists($assetFolder)) {
                mkdir($assetFolder);
            }

            foreach (glob($assetPattern) as $file) {
                $fileName = $this->getFileName($file);
                $content = file_get_contents($file);
                $content = $this->parsePubDirPath($content);
                file_put_contents($assetFolder . '/' . $fileName, $content);
            }
        }

        return $this;
    }

    /**
     * Filter and copy library js files to the frontend directory.
     * @return self
     */
    private function processLibs()
    {
        $libFolder = BP . self::FRONTEND_STATIC_PATH . '/lib/';

        if (!file_exists($libFolder)) {
            mkdir($libFolder);
        }

        $libFiles = glob(BP . self::JS_LIB_PATH_PATTERN);
        $libFiles = $this->filterMinifiedFiles($libFiles);

        foreach ($libFiles as $file) {
            $fileName = $this->getFileName($file);
            copy($file, $libFolder . $fileName);
        }

        return $this;
    }

    /**
     * Retrieve file name by the whole path.
     * @param string $filePath
     * @return string
     */
    private function getFileName($filePath)
    {
        $filePath = explode('/', $filePath);

        return end($filePath);
    }

    /**
     * Replace pub dir placeholder with the correct path.
     * @param string $content
     * @return string
     */
    private function parsePubDirPath($content)
    {
        return str_replace(self::ASSET_PUB_TRIGGER, self::ASSET_PUB_REPLACE, $content);
    }

    /**
     * Generate new deployed version and save it.
     * @return self
     */
    public function generateDeployedVersion()
    {
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

    /**
     * Check and remove files which have minified versions.
     * @param array $files
     * @return array
     */
    function filterMinifiedFiles($files)
    {
        $files = array_flip($files);

        foreach ($files as $file => $unused) {
            $fileNotMinified = str_replace('.min', '', $file);

            if ($fileNotMinified !== $file) {
                unset($files[$fileNotMinified]);
            }
        }

        return array_flip($files);
    }
}
