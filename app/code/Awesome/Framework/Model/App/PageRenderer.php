<?php

namespace Awesome\Framework\Model\App;

use \Awesome\Framework\Model\XmlParser\PageXmlParser;
use \Awesome\Cache\Model\Cache;

class PageRenderer
{
    /**
     * @var PageXmlParser $pageXmlParser
     */
    private $pageXmlParser;

    /**
     * @var \Awesome\Framework\Block\Html $htmlTemplate
     */
    private $htmlTemplate;

    /**
     * @var Cache $cache
     */
    protected $cache;

    /**
     * PageRenderer constructor.
     */
    function __construct()
    {
        $this->pageXmlParser = new PageXmlParser();
        $this->htmlTemplate = new \Awesome\Framework\Block\Html();
        $this->cache = new Cache();
    }

    /**
     * Render the page according to XML handle.
     * @param string $handle
     * @param string $view
     * @return string
     */
    public function render($handle, $view)
    {
        $handle = $this->parseHandle($handle);

        if (!$pageContent = $this->cache->get(Cache::FULL_PAGE_CACHE_KEY, $handle)) {
            if ($this->handleExist($handle, $view)) {
                $structure = $this->pageXmlParser->retrievePageStructure($handle, $view);

                $this->htmlTemplate->setView($view)
                    ->setHandle($handle)
                    ->setHeadStructure($structure['head'])
                    ->setStructure($structure['body']);

                $pageContent = $this->htmlTemplate->toHtml();

                $this->cache->save(Cache::FULL_PAGE_CACHE_KEY, $handle, $pageContent);
            }
        }

        return $pageContent ?? '';
    }

    /**
     * Check if requested page handle exists.
     * @param string $handle
     * @param string $view
     * @return bool
     */
    public function handleExist($handle, $view)
    {
        $handle = $this->parseHandle($handle);

        return $this->pageXmlParser->handleExist($handle, $view);
    }

    /**
     * Parse requested handle into valid page handle.
     * Handle should consists of three parts, missing ones will be added automatically as 'index'.
     * @param string $handle
     * @return string
     */
    private function parseHandle($handle)
    {
        $parts = explode('_', $handle);
        $handle = $parts[0] . '_'           //module
            . ($parts[1] ?? 'index') . '_'  //page
            . ($parts[2] ?? 'index');       //action

        return $handle;
    }
}
