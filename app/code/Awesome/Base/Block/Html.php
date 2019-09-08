<?php

namespace Awesome\Base\Block;

class Html extends \Awesome\Base\Block\Template
{
    protected $template = 'Awesome_Base::html.phtml';

    /**
     * @var string $handle
     */
    protected $handle;

    /**
     * @var array $structure
     */
    protected $structure;

    /**
     * @var \Awesome\Base\Block\Html\Head $headTemplate
     */
    private $headTemplate;

    /**
     * Base Template constructor.
     */
    public function __construct()
    {
        $this->headTemplate = new \Awesome\Base\Block\Html\Head();
        parent::__construct();
    }

    /**
     * Set current page handle
     * @param string $handle
     * @return $this
     */
    public function setHandle($handle)
    {
        $this->handle = $handle;

        return $this;
    }

    /**
     * Set html page structure.
     * @param array $structure
     * @return $this
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * Create and render head part of the page.
     * @return string
     */
    public function getHead()
    {
        $head = '';

        if ($headStructure = $this->structure['head']) {
            $this->headTemplate->setData($headStructure)
                ->setView($this->view);

            $head = $this->headTemplate->toHtml();
        }

        return $head;
    }

    /**
     * Get body class by page handle.
     * @return string
     */
    public function getBodyClass()
    {
        $class = str_replace('-', '', $this->handle);

        return str_replace('_', '-', $class);
    }

    /**
     * Create and render body part of the page.
     * @return string
     */
    public function getBody()
    {
        $body = '';

        if ($containers = $this->structure['body']['children']) {
            foreach ($containers as $container) {
                $body .= $this->getContent($container);
            }
        };

        return $body;
    }

    /**
     * Parse and render blocks recursively.
     * @param array $block
     * @return string
     */
    private function getContent($block)
    {
        $className = $block['class'];
        $template = $block['template'];
        $children = $block['children'] ?? [];
        $data = $block['data'] ?? [];

        /** @var \Awesome\Base\Block\Template $templateClass */
        $templateClass = new $className();
        $templateClass->setView($this->view)
            ->setTemplate($template)
            ->setData($data);

        $content = $templateClass->toHtml();

        foreach ($children as $childName => $child) {
            $content .= $this->getContent($child);
        }

        return $content;
    }
}
