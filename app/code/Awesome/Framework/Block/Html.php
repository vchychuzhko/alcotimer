<?php

namespace Awesome\Framework\Block;

use Awesome\Framework\Block\Html\Head;

class Html extends \Awesome\Framework\Block\Template
{
    /**
     * @inheritDoc
     */
    protected $template = 'Awesome_Framework::html.phtml';

    /**
     * @var Head $headTemplate
     */
    private $headTemplate;

    /**
     * Html constructor.
     * @inheritDoc
     */
    public function __construct($renderer, $name, $template = null, $children = [])
    {
        $this->headTemplate = new Head($renderer, 'head', null, $children['head']);
        parent::__construct($renderer, $name, $template, array_keys($children['body']['children']));
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
        return str_replace('_', '-', str_replace('-', '', $this->renderer->getHandle()));
    }
}
