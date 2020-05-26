<?php

namespace Awesome\Frontend\Block;

use Awesome\Frontend\Block\Html\Head;

class Root extends \Awesome\Frontend\Block\Template
{
    /**
     * @inheritDoc
     */
    protected $template = 'Awesome_Frontend::root.phtml';

    /**
     * @var Head $headTemplate
     */
    private $headTemplate;

    /**
     * Root constructor.
     * @inheritDoc
     */
    public function __construct($renderer, $name, $template = null, $children = [])
    {
        parent::__construct($renderer, $name, $template, array_keys($children['body']['children']));
        $this->headTemplate = new Head($renderer, 'head', null, $children['head']);
    }

    /**
     * @inheritDoc
     */
    public function getChildHtml($blockName = '')
    {
        if ($blockName === 'head') {
            $childHtml = $this->headTemplate->toHtml();
        } else {
            $childHtml = parent::getChildHtml($blockName);
        }

        return $childHtml;
    }

    /**
     * Get body class by page handle.
     * @return string
     */
    public function getBodyClass()
    {
        return str_replace(['-', '_'], ['', '-'], $this->renderer->getHandle());
    }
}
