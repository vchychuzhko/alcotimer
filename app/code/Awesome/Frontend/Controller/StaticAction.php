<?php
declare(strict_types=1);

namespace Awesome\Frontend\Controller;

use Awesome\Framework\Exception\NotFoundException;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\ResponseFactory;
use Awesome\Framework\Model\Logger;
use Awesome\Framework\Model\ResponseInterface;
use Awesome\Frontend\Helper\StaticContentHelper;
use Awesome\Frontend\Model\FrontendState;
use Awesome\Frontend\Model\Generator\RequireJs;
use Awesome\Frontend\Model\Generator\StaticFile;
use Awesome\Frontend\Model\Generator\Styles;
use Awesome\Frontend\Model\Generator\Translation;
use Awesome\Frontend\Model\GeneratorFactory;

class StaticAction extends \Awesome\Framework\Model\AbstractAction
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

    private FrontendState $frontendState;

    private GeneratorFactory $generatorFactory;

    private Logger $logger;

    private Request $request;

    /**
     * StaticAction constructor.
     * @param FrontendState $frontendState
     * @param GeneratorFactory $generatorFactory ,
     * @param Logger $logger
     * @param Request $request
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        FrontendState $frontendState,
        GeneratorFactory $generatorFactory,
        Logger $logger,
        Request $request,
        ResponseFactory $responseFactory
    ) {
        parent::__construct($responseFactory);
        $this->frontendState = $frontendState;
        $this->generatorFactory = $generatorFactory;
        $this->logger = $logger;
        $this->request = $request;
    }

    /**
     * Check if correct static file is requested and generate it.
     * @inheritDoc
     */
    public function execute(Request $request): ResponseInterface
    {
        $path = (string) $this->request->getParam('resource');
        $file = $this->getFile($path);
        $view = $this->getView($path);

        if (!$file || !$view || !$this->minificationMatch($path) || !$type = $this->staticFileMatch($file)) {
            throw new NotFoundException();
        }

        $generator = $this->generatorFactory->create($type);

        $content = $generator->generate($file, $view);

        $response = $this->responseFactory->create()
            ->setContent($content);

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (isset(self::MIME_TYPES[$extension])) {
            $response->setHeader('Content-Type', self::MIME_TYPES[$extension]);
        }

        $this->logger->info(__('Static file was deployed: %1', $path));

        return $response;
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
            $minify = $this->frontendState->isCssMinificationEnabled();
        }
        if ($extension === 'js') {
            $minify = $this->frontendState->isJsMinificationEnabled();
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
        if (RequireJs::match($file)) return RequireJs::class;

        if (Styles::match($file)) return Styles::class;

        if (Translation::match($file)) return Translation::class;

        if (is_file($file) && StaticFile::match($file)) return StaticFile::class;

        return null;
    }
}
