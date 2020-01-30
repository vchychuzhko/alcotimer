<?php

namespace Awesome\Cache\Model;

use Awesome\Framework\App\Http;

class StaticContent
{
    private const STATIC_FOLDER_PATH = '/pub/static/';
    private const DEPLOYED_VERSION_FILE = '/pub/static/deployed_version.txt';
    private const ASSETS_FOLDER_PATH_PATTERN = '/*/*/view/%v/web/%a';
    private const JS_LIB_PATH_PATTERN = '/lib/*/*.js';
    private const PUB_FOLDER_TRIGGER = '{@pubDir}';

    /**
     * @var \Awesome\Framework\Model\Config $config
     */
    private $config;

    /**
     * App constructor.
     */
    public function __construct()
    {
        $this->config = new \Awesome\Framework\Model\Config();
    }

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
            foreach ([Http::FRONTEND_VIEW, Http::BACKEND_VIEW] as $view) {
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
        if (!file_exists(BP . self::STATIC_FOLDER_PATH . $view)) {
            mkdir(BP . self::STATIC_FOLDER_PATH . $view);
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
        rrmdir(BP . self::STATIC_FOLDER_PATH . $view);

        return $this;
    }

    /**
     * Collect, parse and generate css/js files for requested view.
     * @param string $view
     * @return $this
     */
    private function generateAssets($view)
    {
        $staticFolder = BP . self::STATIC_FOLDER_PATH . $view;
        $viewPath = str_replace('%v', $view, self::ASSETS_FOLDER_PATH_PATTERN);
        $baseViewPath = str_replace('%v', Http::BASE_VIEW, self::ASSETS_FOLDER_PATH_PATTERN);
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
                    //@TODO: insert minifying/merging here

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
        $staticFolder = BP . self::STATIC_FOLDER_PATH . $view;

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
     * Retrieve relative path and file name from absolute path.
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
        $pubPath = $this->config->get(Http::WEB_ROOT_CONFIG) ? '/' : '/pub/';

        return str_replace(self::PUB_FOLDER_TRIGGER, $pubPath, $content);
    }

    /**
     * Generate static deployed version and save it.
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
        //@TODO: Resolve situation when frontend folder is missing, but deployed version is present
        return (string) @file_get_contents(BP . self::DEPLOYED_VERSION_FILE);
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
