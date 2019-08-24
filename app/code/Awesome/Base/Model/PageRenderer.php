<?php

namespace Awesome\Base\Model;

class PageRenderer
{
    private const FRONTEND_VIEW = 'frontend';
    private const ADMINHTML_VIEW = 'adminhtml';

    /**
     * @var XmlParser
     */
    private $xmlParser;

    /**
     * @var string $handle
     */
    private $handle;

    /**
     * @var array $structure
     */
    private $structure;

    /**
     * PageRenderer constructor.
     */
    function __construct()
    {
        $this->xmlParser = new \Awesome\Base\Model\XmlParser();
    }

    /**
     * Render the page according to XML handle.
     * @param string $handle
     * @param string $view
     * @return string
     */
    public function render($handle, $view = self::FRONTEND_VIEW)
    {
        $page = '';

        if ($this->handleExist($handle, $view)) {
            $foo = false;
            //parse default
            //parse handle
            //merge
            //execute by block
        }

        return $page;
    }

    /**
     * Check if requested page handle exists.
     * @param string $handle
     * @param string $view
     * @return bool
     */
    public function handleExist($handle, $view = self::FRONTEND_VIEW)
    {
        $handle = $this->parseHandle($handle);
        $this->structure = $this->structure ?? $this->xmlParser->retrievePageStructure($handle);

        return !empty($this->structure);
    }

    /**
     * Parse requested handle into valid page handle.
     * Handle should consists of three parts, missing ones will be added automatically as 'index'.
     * @param string $handle
     * @return string
     */
    private function parseHandle($handle)
    {
        if ($this->handle !== $handle) {
            $handle = str_replace('-', '_', $handle);
            $parts = explode('_', $handle);
            $handle = $parts[0] . '_' //module
                . ($parts[1] ?? 'index') . '_' //page
                . ($parts[2] ?? 'index'); //action

            $this->handle = $handle;
            $this->structure = null;
        }

        return $this->handle;
    }
}
