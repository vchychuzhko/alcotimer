<?php

namespace Awesome\Framework\Model\Http;

use \Awesome\Cache\Model\Cache;

class LayoutHandler
{
    /**
     * @var \Awesome\Framework\XmlParser\PageXmlParser $pageXmlParser
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
     * LayoutHandler constructor.
     */
    function __construct()
    {
        $this->pageXmlParser = new \Awesome\Framework\XmlParser\PageXmlParser();
        $this->htmlTemplate = new \Awesome\Framework\Block\Html();
        $this->cache = new Cache();
    }

    /**
     * Render the page according to XML handle.
     * @param string $handle
     * @param string $view
     * @return string
     */
    public function process($handle, $view)
    {
        $handle = $this->parseHandle($handle);

        if (!$pageContent = $this->cache->get(Cache::FULL_PAGE_CACHE_KEY, $handle)) {
            $structure = $this->pageXmlParser->getPageStructure($handle, $view);

            $this->htmlTemplate->setView($view)
                ->setHandle($handle)
                ->setHeadStructure($structure['head'])
                ->setBodyStructure($structure['body']);

            $pageContent = $this->htmlTemplate->toHtml();

            $this->cache->save(Cache::FULL_PAGE_CACHE_KEY, $handle, $pageContent);
        }

        return $pageContent ?: '';
    }

    /**
     * Check if requested page handle exists.
     * @param string $handle
     * @param string $view
     * @return bool
     */
    public function exist($handle, $view)
    {
        return $this->pageXmlParser->handleExist($handle, $view);
    }

    /**
     * Parse requested handle into valid page handle.
     * Handle should consists of three parts, missing ones will be added automatically as 'index'.
     * @param string $handle
     * @return string
     */
    public function parseHandle($handle)
    {
        $handle = str_replace('/', '_', $handle);
        $parts = explode('_', $handle);
        $handle = $parts[0] . '_'           //module
            . ($parts[1] ?? 'index') . '_'  //page
            . ($parts[2] ?? 'index');       //action

        return $handle;
    }
}
