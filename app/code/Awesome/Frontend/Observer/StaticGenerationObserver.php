<?php
declare(strict_types=1);

namespace Awesome\Frontend\Observer;

use Awesome\Framework\Model\Event;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Http\ActionResolver;
use Awesome\Framework\Model\Http\Request;
use Awesome\Frontend\Helper\StaticContentHelper;
use Awesome\Frontend\Model\Action\StaticGenerationHandler;
use Awesome\Frontend\Model\FrontendState;
use Awesome\Frontend\Model\RequireJs;
use Awesome\Frontend\Model\StaticContent;

class StaticGenerationObserver implements \Awesome\Framework\Model\Event\ObserverInterface
{
    private const STATIC_FILE_PATTERN = '/^(\/pub)?(\/static\/)(version.+?\/)?(%s|%s)\/(%s|\w+_\w+)?\/?(.*)$/';
    private const STATIC_REQUEST_PATTERN = '/^(\/pub)?\/static\/(version.+?\/)?(%s|%s)\/(.*)$/';

    /**
     * @var FrontendState $frontendState
     */
    private $frontendState;

    /**
     * StaticGenerationObserver constructor.
     * @param FrontendState $frontendState
     */
    public function __construct(FrontendState $frontendState)
    {
        $this->frontendState = $frontendState;
    }

    /**
     * Check if missing static file is requested and return action to generate it.
     * @inheritDoc
     */
    public function execute(Event $event): void
    {
        /** @var ActionResolver $actionResolver */
        $actionResolver = $event->getActionResolver();
        /** @var Request $request */
        $request = $event->getRequest();
        $requestPath = $request->getPath();

        if ($this->isStaticFileRequest($requestPath)) {
            $requestedFile = $this->getFilePath($requestPath);

            if (file_exists($requestedFile)) {
                $extension = pathinfo($requestedFile, PATHINFO_EXTENSION);
                $minify = false;

                if ($extension === 'css') {
                    $minify = $this->frontendState->isCssMinificationEnabled();
                } elseif ($extension === 'js') {
                    $minify = $this->frontendState->isJsMinificationEnabled();
                }

                if (StaticContentHelper::isFileMinified($requestPath) === $minify) {
                    $actionResolver->addAction(StaticGenerationHandler::class, ['requested_file' => $requestedFile]);
                }
            } elseif ($requestedFile === RequireJs::RESULT_FILENAME) {
                $actionResolver->addAction(StaticGenerationHandler::class, ['requested_file' => $requestedFile]);
            }
        }
    }

    /**
     * Check if static file is requested.
     * @param string $requestPath
     * @return bool
     */
    private function isStaticFileRequest(string $requestPath): bool
    {
        $match = (bool) preg_match(
            sprintf(self::STATIC_REQUEST_PATTERN, Http::FRONTEND_VIEW, Http::BACKEND_VIEW), $requestPath, $matches
        );
        @list($unused, $pub) = $matches;

        return $match && (($pub === '') === $this->frontendState->isPubRoot());
    }

    /**
     * Convert requested path to file path.
     * @param string $requestPath
     * @return string
     */
    private function getFilePath(string $requestPath): string
    {
        preg_match(
            sprintf(self::STATIC_FILE_PATTERN, Http::FRONTEND_VIEW, Http::BACKEND_VIEW, StaticContent::LIB_FOLDER_PATH),
            $requestPath,
            $matches
        );
        @list($unused, $pub, $static, $version, $view, $module, $file) = $matches;
        StaticContentHelper::removeMinificationFlag($file);

        if ($module === StaticContent::LIB_FOLDER_PATH) {
            $path = BP . '/' . StaticContent::LIB_FOLDER_PATH . '/' . $file;
        } elseif (strpos($module, '_') !== false) {
            $path = APP_DIR  . '/' . str_replace('_', '/', $module) . '/view/' . $view . '/web/' . $file;

            if (!file_exists($path)) {
                $path = preg_replace('/(\/view\/)(\w+)(\/)/', '$1' . Http::BASE_VIEW . '$3', $path);
            }
        } else {
            $path = (string) $file;
        }

        return $path;
    }
}
