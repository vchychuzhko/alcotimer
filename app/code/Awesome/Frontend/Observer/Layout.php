<?php

namespace Awesome\Frontend\Observer;

use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Router;
use Awesome\Frontend\Model\Action\LayoutRenderer;
use Awesome\Frontend\Model\XmlParser\Layout as LayoutXmlParser;

class Layout implements \Awesome\Framework\Model\Event\ObserverInterface
{
    public const HOMEPAGE_HANDLE_CONFIG = 'web/homepage';

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var LayoutXmlParser $layoutXmlParser
     */
    private $layoutXmlParser;

    /**
     * Layout constructor.
     */
    public function __construct()
    {
        $this->config = new Config();
        $this->layoutXmlParser = new LayoutXmlParser();
    }

    /**
     * Map page layout renderer by requested path.
     * @inheritDoc
     */
    public function execute($event)
    {
        /** @var Request $request */
        $request = $event->getRequest();
        /** @var Router $router */
        $router = $event->getRouter();
        $path = $request->getPath();
        $view = $router->getView();

        if ($path === '/') {
            $path = $this->getHomepageHandle();
        }
        $handle = $this->parse($path);
// @todo: parse handle in Http? one for all?
        if ($this->exist($handle, $view)) {
            $router->addAction(new LayoutRenderer($handle, $view));
        }
    }

    /**
     * Check if requested page handle exist specified view.
     * @param string $handle
     * @param string $view
     * @return bool
     */
    private function exist($handle, $view)
    {
        return in_array($handle, $this->layoutXmlParser->getHandlesForView($view));
    }

    /**
     * Parse requested path into a valid page layout handle.
     * Handle should consists of three parts, missing ones will be added automatically as 'index'.
     * @param string $path
     * @return string
     */
    private function parse($path)
    {
        $parts = explode('_', str_replace('/', '_', $path));

        return $parts[0] . '_'              //module
            . ($parts[1] ?? 'index') . '_'  //page
            . ($parts[2] ?? 'index');       //action
    }

    /**
     * Return homepage handle.
     * @return string
     */
    private function getHomepageHandle()
    {
        return (string) $this->config->get(self::HOMEPAGE_HANDLE_CONFIG);
    }
}
