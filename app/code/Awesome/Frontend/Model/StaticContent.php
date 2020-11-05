<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Framework\Model\AppState;
use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Logger;

class StaticContent
{
    public const STATIC_FOLDER_PATH = '/pub/static/';
    public const LIB_FOLDER_PATH = 'lib';
    private const DEPLOYED_VERSION_FILE = 'deployed_version.txt';

    private const STATIC_PATH_PATTERN = '/*/*/view/%s/web/%s';
    private const LIB_PATH_PATTERN = '/lib/*/*.js';
    private const STATIC_FILE_PATTERN = '/(.*\/)app\/code\/(\w+)\/(\w+)\/view\/(\w+)\/web\/(.*)$/';
    private const LIB_FILE_PATTERN = '/\/lib\/\w+\/.*$/';

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
     * @var Logger $logger
     */
    private $logger;

    /**
     * @var RequireJs $requireJs
     */
    private $requireJs;

    /**
     * @var int $deployedVersion
     */
    private $deployedVersion;

    /**
     * StaticContent constructor.
     * @param AppState $appState
     * @param FileManager $fileManager
     * @param Logger $logger
     * @param RequireJs $requireJs
     */
    public function __construct(AppState $appState, FileManager $fileManager, Logger $logger, RequireJs $requireJs)
    {
        $this->appState = $appState;
        $this->fileManager = $fileManager;
        $this->logger = $logger;
        $this->requireJs = $requireJs;
    }

    /**
     * Deploy static files for a specified view.
     * Process both views if not specified.
     * @param string $view
     * @return $this
     */
    public function deploy(string $view = ''): self
    {
        $this->generateDeployedVersion();

        if ($view === '') {
            foreach ([Http::FRONTEND_VIEW, Http::BACKEND_VIEW] as $httpView) {
                $this->processView($httpView);
            }
        } else {
            $this->processView($view);
        }

        return $this;
    }

    /**
     * Perform all needed steps for specified view.
     * @param string $view
     * @return $this
     */
    private function processView(string $view): self
    {
        $this->fileManager->removeDirectory(BP . self::STATIC_FOLDER_PATH . $view);
        $this->fileManager->createDirectory(BP . self::STATIC_FOLDER_PATH . $view);

        $this->generate($view);
        $this->requireJs->generate($view, $this->getDeployedVersion());
        $this->logger->info(sprintf('Static files were deployed for "%s" view', $view));

        return $this;
    }

    /**
     * Collect, parse and generate css/js files for requested view.
     * @param string $view
     * @return $this
     */
    private function generate(string $view): self
    {
        $cssPattern = sprintf(self::STATIC_PATH_PATTERN, '{' . Http::BASE_VIEW . ',' . $view . '}', 'css');
        $jsPattern = sprintf(self::STATIC_PATH_PATTERN, '{' . Http::BASE_VIEW . ',' . $view . '}', 'js');

        foreach (glob(APP_DIR . $cssPattern, GLOB_ONLYDIR | GLOB_BRACE) as $folder) {
            $files = $this->fileManager->scanDirectory($folder, true, 'css');
            $this->filterMinifiedFiles($files);

            foreach ($files as $file) {
                $this->generateFile($file, $view);
            }
        }

        foreach (glob(APP_DIR . $jsPattern, GLOB_ONLYDIR | GLOB_BRACE) as $folder) {
            $files = $this->fileManager->scanDirectory($folder, true, 'js');
            $this->filterMinifiedFiles($files);

            foreach ($files as $file) {
                $this->generateFile($file, $view);
            }
        }

        $libFiles = glob(BP . self::LIB_PATH_PATTERN);
        $this->filterMinifiedFiles($libFiles);

        foreach ($libFiles as $libFile) {
            $this->generateLibFile($libFile, $view);
        }

        return $this;
    }

    /**
     * Deploy static file for specified view.
     * @param string $path
     * @param string $view
     * @return $this
     */
    public function deployFile(string $path, string $view): self
    {
        $this->generateDeployedVersion();

        if (!is_dir(BP . self::STATIC_FOLDER_PATH . $view)) {
            $this->fileManager->createDirectory(BP . self::STATIC_FOLDER_PATH . $view);
        }

        if ($path === RequireJs::RESULT_FILENAME) {
            $this->requireJs->generate($view, $this->getDeployedVersion());
        } else {
            $path = BP . '/' . ltrim(str_replace(BP, '', $path), '/');

            if (preg_match(self::LIB_FILE_PATTERN, $path)) {
                $this->generateLibFile($path, $view);
            } else {
                $this->generateFile($path, $view);
            }
        }
        $this->logger->info(sprintf('Static file "%s" was deployed for "%s" view', $path, $view));

        return $this;
    }

    /**
     * Parse and generate css/js file for requested view.
     * Absolute path is required.
     * @param string $path
     * @param string $view
     * @return $this
     */
    private function generateFile(string $path, string $view): self
    {
        $content = $this->fileManager->readFile($path, false);
        $this->parsePubDirPath($content);

        $staticPath = preg_replace(self::STATIC_FILE_PATTERN, '/$2_$3/$5', $path);

        $this->fileManager->createFile(BP . self::STATIC_FOLDER_PATH . $view . $staticPath, $content);

        return $this;
    }

    /**
     * Copy library file for requested view.
     * Absolute path is required.
     * @param string $path
     * @param string $view
     * @return $this
     */
    private function generateLibFile(string $path, string $view): self
    {
        $staticPath = str_replace(BP, '', $path);
        $this->fileManager->copyFile($path, BP . self::STATIC_FOLDER_PATH . $view . $staticPath);

        return $this;
    }

    /**
     * Replace pub dir placeholder with the current pub URL path.
     * @param string $content
     * @return void
     */
    private function parsePubDirPath(string &$content): void
    {
        $pubPath = $this->appState->isPubRoot() ? '/' : '/pub/';

        $content = str_replace(self::PUB_FOLDER_TRIGGER, $pubPath, $content);
    }

    /**
     * Generate static deployed version and save it.
     * @return $this
     */
    public function generateDeployedVersion(): self
    {
        $this->deployedVersion = time();
        $this->fileManager->createFile(BP . self::STATIC_FOLDER_PATH . self::DEPLOYED_VERSION_FILE, (string) $this->deployedVersion, true);

        return $this;
    }

    /**
     * Get current static deployed version.
     * @return int|null
     */
    public function getDeployedVersion(): ?int
    {
        if (!$this->deployedVersion) {
            $deployedVersion = $this->fileManager->readFile(BP . self::STATIC_FOLDER_PATH . self::DEPLOYED_VERSION_FILE);
            $this->deployedVersion = $deployedVersion ? (int) $deployedVersion : null;
        }

        return $this->deployedVersion;
    }

    /**
     * Filter files which have minified versions.
     * @param array $files
     * @return void
     */
    private function filterMinifiedFiles(array &$files): void
    {
        $files = array_flip($files);

        foreach ($files as $file => $unused) {
            $fileNotMinified = str_replace('.min', '', $file);

            if ($fileNotMinified !== $file) {
                unset($files[$fileNotMinified]);
            }
        }

        $files = array_flip($files);
    }
}
