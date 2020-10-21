<?php
declare(strict_types=1);

namespace Awesome\Frontend\Observer;

use Awesome\Framework\Model\Event;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Router;
use Awesome\Frontend\Model\Action\StaticGenerationHandler;
use Awesome\Frontend\Model\StaticContent;

class StaticGenerationObserver implements \Awesome\Framework\Model\Event\ObserverInterface
{
    private const STATIC_FILE_PATTERN = '/^(\/pub)?\/static\/(version.+?\/)?(%s|%s)\/(%s|\w+_\w+)?\/?(.*)$/';
    private const STATIC_REQUEST_PATTERN = '/^(\/pub)?\/static\//';

    /**
     * Check if missing static file is requested and return action to generate it.
     * @inheritDoc
     */
    public function execute(Event $event): void
    {
        /** @var Request $request */
        $request = $event->getRequest();
        $requestPath = $request->getPath();

        if ($this->isStaticFileRequest($requestPath)) {
            $requestedFile = $this->getFilePath($requestPath);

            if (file_exists($requestedFile)) {
                /** @var Router $router */
                $router = $event->getRouter();

                $router->addAction(StaticGenerationHandler::class, ['requested_file' => $requestedFile]);
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
        return (bool) preg_match(self::STATIC_REQUEST_PATTERN, $requestPath);
    }

    /**
     * Convert requested path to file path.
     * @param string $requestPath
     * @return string
     */
    private function getFilePath(string $requestPath): string
    {
        $path = preg_replace(
            sprintf(self::STATIC_FILE_PATTERN, Http::FRONTEND_VIEW, Http::BACKEND_VIEW, StaticContent::LIB_FOLDER_PATH),
            '$3::$4::$5',
            $requestPath
        );
        @list($view, $module, $file) = explode('::', $path);

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
