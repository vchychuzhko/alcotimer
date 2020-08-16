<?php

namespace Awesome\Frontend\Model\Action;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Response;
use Awesome\Framework\Model\Http\Response\HtmlResponse;

class ErrorLayoutHandler extends \Awesome\Frontend\Model\Action\LayoutHandler
{
    public const FORBIDDEN_PAGE_HANDLE = 'forbidden_index_index';
    public const NOTFOUND_PAGE_HANDLE = 'notfound_index_index';

    /**
     * Render error html page.
     * @inheritDoc
     * @throws \Exception
     */
    public function execute($request)
    {
        $view = $request->getView();

        if ($request->getRedirectStatusCode() === Request::FORBIDDEN_REDIRECT_CODE && $this->showForbiddenPage()) {
            $handle = self::FORBIDDEN_PAGE_HANDLE;
            $status = Response::FORBIDDEN_STATUS_CODE;
        } else {
            $handle = self::NOTFOUND_PAGE_HANDLE;
            $status = Response::NOTFOUND_STATUS_CODE;
        }

        if (!$pageContent = $this->cache->get(Cache::FULL_PAGE_CACHE_KEY, $handle . '_' . $view)) {
            $pageContent = $this->renderPage($handle, $view);

            $this->cache->save(Cache::FULL_PAGE_CACHE_KEY, $handle . '_' . $view, $pageContent);
        }

        return new HtmlResponse($pageContent, $status);
    }

    /**
     * Check if it is allowed to show 403 Forbidden page.
     * @return bool
     */
    private function showForbiddenPage()
    {
        return (bool) $this->config->get(Http::SHOW_FORBIDDEN_CONFIG);
    }
}
