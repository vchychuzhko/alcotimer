<?php

namespace Awesome\Framework\Handler;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Block\Html;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\TemplateRenderer;
use Awesome\Framework\XmlParser\LayoutXmlParser;

class LayoutHandler extends \Awesome\Framework\Model\Handler\AbstractHandler
{
    /**
     * @var LayoutXmlParser $layoutXmlParser
     */
    private $layoutXmlParser;

    /**
     * @var string $view
     */
    private $view;

    /**
     * LayoutHandler constructor.
     */
    function __construct()
    {
        $this->layoutXmlParser = new LayoutXmlParser();
        parent::__construct();
    }

    /**
     * Render the page according to XML handle.
     * @inheritDoc
     */
    public function process($handle)
    {
        $handle = $this->parse($handle);

        if (!$pageContent = $this->cache->get(Cache::FULL_PAGE_CACHE_KEY, $handle)) {
            $structure = $this->layoutXmlParser->setView($this->view)
                ->get($handle);

            $templateRenderer = new TemplateRenderer($handle, $this->view, $structure['body']['children']);
            $html = new Html($templateRenderer, 'root', null, $structure);

            $pageContent = $html->toHtml();

            $this->cache->save(Cache::FULL_PAGE_CACHE_KEY, $handle, $pageContent);
        }

        return $pageContent ?: '';
    }

    /**
     * @inheritDoc
     */
    public function exist($handle)
    {
        $handle = $this->parse($handle);

        return in_array($handle, $this->layoutXmlParser->getHandlesForView($this->view));
    }

    /**
     * Handle should consists of three parts, missing ones will be added automatically as 'index'.
     * @inheritDoc
     * @return string
     */
    public function parse($handle)
    {
        $handle = str_replace('/', '_', $handle);
        $parts = explode('_', $handle);
        $handle = $parts[0] . '_'           //module
            . ($parts[1] ?? 'index') . '_'  //page
            . ($parts[2] ?? 'index');       //action

        return $handle;
    }

    /**
     * Set current page view.
     * @param string $view
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Parse http request.
     * @return Request
     */
    public function parseRequest()
    {
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === 443
            ? Request::SCHEME_HTTPS
            : Request::SCHEME_HTTP;
        $url = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];
        $parameters = [];
        $redirectStatus = $_SERVER['REDIRECT_STATUS'] ?? null;
        $userIPAddress = $_SERVER['REMOTE_ADDR'];

        if ($_GET) {
            $parameters = array_merge($parameters, $_GET);
        }

        if ($_POST) {
            $parameters = array_merge($parameters, $_POST);
        }

        return new Request($url, $method, $parameters, $redirectStatus, $userIPAddress);
    }
}
