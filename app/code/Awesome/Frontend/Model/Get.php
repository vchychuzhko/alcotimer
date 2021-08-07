<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Framework\Model\Action\HttpErrorAction;
use Awesome\Framework\Model\Action\MaintenanceAction;
use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Event\EventManager;
use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Http\ActionResolver;
use Awesome\Framework\Model\Http\ResponseFactory;
use Awesome\Framework\Model\Locale;
use Awesome\Framework\Model\Logger;
use Awesome\Framework\Model\Maintenance;
use Awesome\Frontend\Helper\StaticContentHelper;
use Awesome\Frontend\Model\FrontendState;
use Awesome\Frontend\Model\Generator\RequireJs;
use Awesome\Frontend\Model\Generator\StaticFile;
use Awesome\Frontend\Model\Generator\Styles;
use Awesome\Frontend\Model\Generator\Translation;
use Awesome\Frontend\Model\GeneratorFactory;

class Get extends \Awesome\Framework\Model\Http
{
    private const STATIC_FILE_PATTERN = '/^(%s|%s)\/(lib|\w+_\w+)?\/?(.*)$/';

    /**
     * Mime types for static files.
     */
    private const MIME_TYPES = [
        'css'   => 'text/css',
        'html'  => 'text/html',
        'js'    => 'text/javascript',
        'json'  => 'application/json',
        'eot'   => 'application/vnd.ms-fontobject',
        'ttf'   => 'font/ttf',
        'otf'   => 'font/otf',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'svg'   => 'image/svg+xml',
    ];

    /**
     * @var FrontendState $appState
     */
    protected $appState;

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * @var GeneratorFactory $generatorFactory
     */
    private $generatorFactory;

    /**
     * @var ResponseFactory $responseFactory
     */
    private $responseFactory;

    /**
     * Get constructor.
     * @param ActionResolver $actionResolver
     * @param Config $config
     * @param EventManager $eventManager
     * @param FileManager $fileManager
     * @param FrontendState $frontendState
     * @param GeneratorFactory $generatorFactory,
     * @param Locale $locale
     * @param Logger $logger
     * @param Maintenance $maintenance
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ActionResolver $actionResolver,
        Config $config,
        EventManager $eventManager,
        FileManager $fileManager,
        FrontendState $frontendState,
        GeneratorFactory $generatorFactory,
        Locale $locale,
        Logger $logger,
        Maintenance $maintenance,
        ResponseFactory $responseFactory
    ) {
        parent::__construct($actionResolver, $frontendState, $config, $eventManager, $locale, $logger, $maintenance);
        $this->fileManager = $fileManager;
        $this->generatorFactory = $generatorFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Check if correct static file is requested and generate it.
     * @inheritDoc
     */
    public function run(): void
    {
        try {
            $request = $this->getRequest();
            $this->locale->init($request);

            if (!$this->isMaintenance()) {
                $path = (string) $request->getParam('resource');
                $file = $this->getFile($path);
                $view = $this->getView($path);

                if ($file && $view && $this->minificationMatch($path) && $type = $this->staticFileMatch($file)) {
                    $generator = $this->generatorFactory->create($type);

                    $generator->generate($file, $view);

                    $content = $this->fileManager->readFile(BP . StaticContent::STATIC_FOLDER_PATH . $path);

                    $response = $this->responseFactory->create()
                        ->setContent($content);

                    $extension = pathinfo($path, PATHINFO_EXTENSION);

                    if (isset(self::MIME_TYPES[$extension])) {
                        $response->setHeader('Content-Type', self::MIME_TYPES[$extension]);
                    }

                    $this->logger->info(sprintf('Static file was deployed: "%s"', $path));
                } else {
                    $action = $this->actionResolver->getAction();

                    $response = $action->execute($request);
                }
            } else {
                /** @var MaintenanceAction $maintenanceAction */
                $maintenanceAction = $this->actionResolver->getMaintenanceAction();

                $response = $maintenanceAction->execute($request);
            }
        } catch (\Exception $e) {
            $errorMessage = get_class_name($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString();

            $this->logger->error($errorMessage);

            $errorAction = new HttpErrorAction(
                $errorMessage,
                $this->appState->isDeveloperMode(),
                isset($request) ? $request->getAcceptType() : null
            );

            $response = $errorAction->execute();
        }

        $response->proceed();
    }

    /**
     * Convert requested path to file path.
     * @param string $path
     * @return string
     */
    private function getFile(string $path): string
    {
        preg_match(
            sprintf(self::STATIC_FILE_PATTERN, Http::FRONTEND_VIEW, Http::BACKEND_VIEW),
            $path,
            $matches
        );
        @list($unused, $view, $module, $file) = $matches;
        $file = StaticContentHelper::removeMinificationFlag((string) $file);

        if ($module === 'lib') {
            $file = BP . '/lib/' . $file;
        } elseif ($module && strpos($module, '_') !== false) {
            $file = APP_DIR  . '/' . str_replace('_', '/', $module) . '/view/' . $view . '/web/' . $file;

            if (!file_exists($file)) {
                $file = preg_replace('/(\/view\/)(\w+)(\/)/', '$1' . Http::BASE_VIEW . '$3', $file);
            }
        }

        return $file;
    }

    /**
     * Extract view for requested file path.
     * @param string $path
     * @return string|null
     */
    private function getView(string $path): ?string
    {
        preg_match(
            sprintf(self::STATIC_FILE_PATTERN, Http::FRONTEND_VIEW, Http::BACKEND_VIEW),
            $path,
            $matches
        );

        return $matches[1] ?? null;
    }

    /**
     * Check if minification is correct for requested file.
     * @param string $file
     * @return bool
     */
    private function minificationMatch(string $file): bool
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if ($extension === 'css') {
            $minify = $this->appState->isCssMinificationEnabled();
        } elseif ($extension === 'js') {
            $minify = $this->appState->isJsMinificationEnabled();
        }

        return !isset($minify) || StaticContentHelper::isFileMinified($file) === $minify;
    }

    /**
     * Check if requested static file can be generated and return generator type.
     * @param string $file
     * @return string|null
     */
    private function staticFileMatch(string $file): ?string
    {
        switch (true) {
            case RequireJs::match($file):
                $type = RequireJs::class;
                break;
            case Styles::match($file):
                $type = Styles::class;
                break;
            case Translation::match($file):
                $type = Translation::class;
                break;
            case (is_file($file) && StaticFile::match($file)):
                $type = StaticFile::class;
                break;
            default:
                $type = null;
                break;
        }

        return $type;
    }
}
