<?php

namespace Awesome\Base\Model\App;

use \Awesome\Base\Model\XmlParser\PageXmlParser;

class PageRenderer
{
    private const FULL_PAGE_CACHE_KEY = 'full-page';

    /**
     * @var PageXmlParser $pageXmlParser
     */
    private $pageXmlParser;

    /**
     * @var \Awesome\Base\Block\Html $htmlTemplate
     */
    private $htmlTemplate;

    /**
     * @var \Awesome\Cache\Model\Cache $cache
     */
    protected $cache;

    /**
     * PageRenderer constructor.
     */
    function __construct()
    {
        $this->htmlTemplate = new \Awesome\Base\Block\Html();
        $this->pageXmlParser = new PageXmlParser();
        $this->cache = new \Awesome\Cache\Model\Cache();
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

        if (!$page = $this->cache->get(self::FULL_PAGE_CACHE_KEY, $handle)) {
            if ($this->handleExist($handle, $view)) {
                $structure = $this->pageXmlParser->retrievePageStructure($handle, $view);

                $this->htmlTemplate->setPageData($handle, $view);
                $this->htmlTemplate->setStructure($structure);
                $pageContent = $this->htmlTemplate->toHtml();
                $page['content'] = $pageContent;

                $this->cache->save(self::FULL_PAGE_CACHE_KEY, $handle, $page);
            }
        }

        return $page['content'] ?? '';
    }

    /**
     * Check if requested page handle exists.
     * @param string $handle
     * @param string $view
     * @return bool
     */
    public function handleExist($handle, $view )
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
        $handle = $parts[0] . '_' //module
            . ($parts[1] ?? 'index') . '_' //page
            . ($parts[2] ?? 'index'); //action

        return $handle;
    }
}
