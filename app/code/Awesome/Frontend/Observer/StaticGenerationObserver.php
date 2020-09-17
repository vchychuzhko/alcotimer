<?php

namespace Awesome\Frontend\Observer;

use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Router;
use Awesome\Frontend\Model\Action\StaticGenerationHandler;

class StaticGenerationObserver implements \Awesome\Framework\Model\Event\ObserverInterface
{
    private const STATIC_FILE_PATTERN = '/^\/(pub\/)?(static\/)(version.+?\/)?(%s|%s)\/(\w+)\/(.*)$/';
    private const STATIC_REQUEST_PATTERN = '/^\/(pub\/)?(static\/)/';

    /**
     * Check if missing static file is requested and return action to generate it.
     * @inheritDoc
     */
    public function execute($event)
    {
        /** @var Router $router */
        $router = $event->getRouter();
        /** @var Request $request */
        $request = $event->getRequest();

        if ($this->isStaticFileRequest($request) && $this->requestedFileExists($request)) {
            $router->addAction(StaticGenerationHandler::class);
        }
    }

    /**
     * Check if static file is requested.
     * @param Request $request
     * @return bool
     */
    private function isStaticFileRequest($request)
    {
        return (bool) preg_match(self::STATIC_REQUEST_PATTERN, $request->getPath());
    }

    /**
     * Check if missing static file exists.
     * @param Request $request
     * @return bool
     */
    private function requestedFileExists($request)
    {
        $path = preg_replace(
            sprintf(self::STATIC_FILE_PATTERN, Http::FRONTEND_VIEW, Http::BACKEND_VIEW),
            '$4::$5::$6',
            $request->getPath()
        );
        @list($view, $module, $file) = explode('::', $path);

        if ($module === 'lib') {
            $asset = BP . '/' . $module . '/' . $file;
        } else {
            $asset = APP_DIR  . '/' . str_replace('_', '/', $module) . '/view/' . $view . '/web/' . $file;
        }

        return file_exists($asset);
    }
}
