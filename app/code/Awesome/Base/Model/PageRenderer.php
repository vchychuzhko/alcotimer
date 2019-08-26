<?php

namespace Awesome\Base\Model;

class PageRenderer
{
    private const BASE_TEMPLATE_PATH = '/Awesome/Base/view/base/templates/base.phtml';
    private const FRONTEND_VIEW = 'frontend';
    private const ADMINHTML_VIEW = 'adminhtml';

    /**
     * @var \Awesome\Base\Model\PageXmlParser $pageXmlParser
     */
    private $pageXmlParser;

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
        $this->pageXmlParser = new \Awesome\Base\Model\PageXmlParser();
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
            $page = <<<HTML
<!DOCTYPE html>
<html lang="en">
HTML;

            if ($head = $this->structure['head']) {
                $this->headRenderer->setData($head);
                $page .= $this->headRenderer->toHtml();
            }

            if ($body = $this->structure['body']) {
                $bodyClass = $this->getBodyClass();

                $page .= <<<HTML
<body class="$bodyClass">
HTML;
                $page .= 'body';

                $page .= <<<HTML
</body>
HTML;
            }

            $page .= <<<HTML
</html>
HTML;
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
        $this->structure = $this->structure ?? $this->pageXmlParser->retrievePageStructure($handle, $view);

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
