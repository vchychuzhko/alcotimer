<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Framework\Model\AppState;
use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http;

class StaticContent
{
    public const STATIC_FOLDER_PATH = '/pub/static/';
    public const LIB_FOLDER_PATH = 'lib';
    private const DEPLOYED_VERSION_FILE = '/pub/static/deployed_version.txt';
    private const ASSETS_FOLDER_PATH_PATTERN = '/*/*/view/%v/web/%a';
    private const JS_LIB_PATH_PATTERN = '/lib/*/*.js';
    private const PUB_FOLDER_TRIGGER = '{@pubDir}';

    /**
     * @var AppState $appState
     */
    private $appState;

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * StaticContent constructor.
     * @param AppState $appState
     * @param FileManager $fileManager
     */
    public function __construct(AppState $appState, FileManager $fileManager)
    {
        $this->appState = $appState;
        $this->fileManager = $fileManager;
    }

    /**
     * Deploy static files for a specified view.
     * Process both views if not specified.
     * @param string $view
     * @return $this
     */
    public function deploy(string $view = ''): self
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
     * @param string $view
     * @return $this
     */
    private function processView(string $view): self
    {
        if (!file_exists(BP . self::STATIC_FOLDER_PATH . $view)) {
            $this->fileManager->createDirectory(BP . self::STATIC_FOLDER_PATH . $view);
        }

        $this->removeStatic($view);
        $this->generateAssets($view);
        $this->processLibs($view);

        return $this;
    }

    /**
     * Remove all static files related to provided view, including directory.
     * @param string $view
     * @return $this
     */
    private function removeStatic(string $view): self
    {
        $this->fileManager->removeDirectory(BP . self::STATIC_FOLDER_PATH . $view);

        return $this;
    }

    /**
     * Collect, parse and generate css/js files for requested view.
     * @param string $view
     * @return $this
     */
    private function generateAssets(string $view): self
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
                $assetFiles = $this->fileManager->scanDirectory($assetFolder, true, '/\.' . $assetFormat .'$/');

                foreach ($assetFiles as $assetFile) {
                    [$folder, $file] = $this->getFilePath($assetFile);
                    $this->fileManager->createDirectory($staticFolder . $folder);

                    $content = $this->fileManager->readFile($assetFile, false);
                    $content = $this->parsePubDirPath($content);
                    //@TODO: insert minifying/merging here

                    $this->fileManager->createFile($staticFolder . $folder . '/' . $file, $content);
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
    private function processLibs(string $view): self
    {
        $staticFolder = BP . self::STATIC_FOLDER_PATH . $view;

        $libFiles = glob(BP . self::JS_LIB_PATH_PATTERN);
        $libFiles = $this->filterMinifiedFiles($libFiles);

        foreach ($libFiles as $libFile) {
            [$folder, $file] = $this->getFilePath($libFile, true);
            $this->fileManager->createDirectory($staticFolder . $folder);

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
    private function getFilePath(string $path, bool $isLib = false): array
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
    private function parsePubDirPath(string $content): string
    {
        $pubPath = $this->appState->isPubRoot() ? '/' : '/pub/';

        return str_replace(self::PUB_FOLDER_TRIGGER, $pubPath, $content);
    }

    /**
     * Generate static deployed version and save it.
     * @return $this
     */
    public function generateDeployedVersion(): self
    {
        $this->fileManager->createFile(BP . self::DEPLOYED_VERSION_FILE, (string) time(), true);

        return $this;
    }

    /**
     * Get current static deployed version.
     * @return string
     */
    public function getDeployedVersion(): string
    {
        //@TODO: Resolve situation when frontend folder is missing, but deployed version is present
        return $this->fileManager->readFile(BP . self::DEPLOYED_VERSION_FILE);
    }

    /**
     * Filter files which have minified versions.
     * @param array $files
     * @return array
     */
    private function filterMinifiedFiles(array $files): array
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
