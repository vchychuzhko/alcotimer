<?php

namespace Awesome\Cache\Model;

use Awesome\Base\Model\App;

class StaticContent
{
    private const DEPLOYED_VERSION_FILE = '/pub/static/deployed_version.txt';
    private const ASSET_PUB_TRIGGER = '{@pubDir}';
    private const ASSET_PUB_REPLACE = '/';
    private const STATIC_FOLDER_PATH = '/pub/static';
    private const ASSET_FOLDER_PATH_PATTERN = '/*/*/view/%v/web/%a';
    private const JS_LIB_PATH_PATTERN = '/lib/*/*.js';

    /**
     * Deploy static files for needed view.
     * Process both views if not specified.
     * @param string $view
     * @return $this
     */
    public function deploy($view = '')
    {
        if ($view) {
            $this->processView($view);
        } else {
            foreach ([App::FRONTEND_VIEW, App::BACKEND_VIEW] as $view) {
                $this->processView($view);
            }
        }

        $this->generateDeployedVersion();

        return $this;
    }

    /**
     * Perform all needed steps for specified view.
     * @param $view
     * @return $this
     */
    private function processView($view)
    {
        if (!file_exists(BP . self::STATIC_FOLDER_PATH . '/' . $view)) {
            mkdir(BP . self::STATIC_FOLDER_PATH . '/' . $view);
        }

        $this->removeStatic($view);
        $this->generateAssets($view);
        $this->processLibs($view);

        return $this;
    }

    /**
     * Remove all static files related to needed view, including directory.
     * @param string $view
     * @return $this
     */
    private function removeStatic($view)
    {
        rrmdir(BP . self::STATIC_FOLDER_PATH . '/' . $view);

        return $this;
    }

    /**
     * Collect, parse and generate css/js files for requested view.
     * @param string $view
     * @return $this
     */
    private function generateAssets($view)
    {
        $staticFolder = BP . self::STATIC_FOLDER_PATH . '/' . $view;
        $viewPath = str_replace('%v', $view, self::ASSET_FOLDER_PATH_PATTERN);
        $baseViewPath = str_replace('%v', App::BASE_VIEW, self::ASSET_FOLDER_PATH_PATTERN);
        $assets = [
            'css_base' => str_replace('%a', 'css', $baseViewPath),
            'css_view' => str_replace('%a', 'css', $viewPath),
            'js_base' => str_replace('%a', 'js', $baseViewPath),
            'js_view' => str_replace('%a', 'js', $viewPath)
        ];

        foreach ($assets as $assetFormat => $assetPattern) {
            $assetFormat = strstr($assetFormat, '_', true);

            foreach (glob(APP_DIR . $assetPattern, GLOB_ONLYDIR) as $assetFolder) {
                $assetFiles = rscandir($assetFolder, '/\.' . $assetFormat .'$/');

                foreach ($assetFiles as $assetFile) {
                    list($folder, $file) = $this->getFilePath($assetFile);
                    @mkdir($staticFolder . $folder, 0777, true);

                    $content = file_get_contents($assetFile);
                    $content = $this->parsePubDirPath($content);

                    file_put_contents($staticFolder . $folder . '/' . $file, $content);
                }
            }
        }

        return $this;
    }

    /**
     * Filter and copy library files to the view directory.
     * @param string $view
     * @return $this
     */
    private function processLibs($view)
    {
        $staticFolder = BP . self::STATIC_FOLDER_PATH . '/' . $view;

        $libFiles = glob(BP . self::JS_LIB_PATH_PATTERN);
        $libFiles = $this->filterMinifiedFiles($libFiles);

        foreach ($libFiles as $libFile) {
            list($folder, $file) = $this->getFilePath($libFile, true);
            @mkdir($staticFolder . $folder, 0777, true);

            copy($libFile, $staticFolder . $folder . $file);
        }

        return $this;
    }

    /**
     * Retrieve file name by the whole path.
     * @param string $path
     * @param bool $isLib
     * @return array
     */
    private function getFilePath($path, $isLib = false)
    {
        $path = str_replace(DS, '/', $path);

        if ($isLib) {
            $folder = str_replace(BP, '', $path);
        } else {
            $folder = ltrim(str_replace(APP_DIR, '', $path), '/');
            $folder = str_replace_first('/', '_', $folder);
            $folder = '/' . preg_replace('/view\/\w*\/web\//', '', $folder);
        }

        $file = explode('/', $folder);
        $file = end($file);
        $folder = str_replace($file, '', $folder);

        return [
            $folder,
            $file
        ];
    }

    /**
     * Replace pub dir placeholder with the current pub URL path.
     * @param string $content
     * @return string
     */
    private function parsePubDirPath($content)
    {
        //@TODO: get pub_path from config
        return str_replace(self::ASSET_PUB_TRIGGER, self::ASSET_PUB_REPLACE, $content);
    }

    /**
     * Generate new deployed version and save it.
     * @return $this
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

        return (string) $version;
    }

    /**
     * Check and remove files which have minified versions.
     * @param array $files
     * @return array
     */
    private function filterMinifiedFiles($files)
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
