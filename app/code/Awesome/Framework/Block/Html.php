<?php

namespace Awesome\Framework\Block;

class Html extends \Awesome\Framework\Block\Template
{
    /**
     * @inheritDoc
     */
    protected $template = 'Awesome_Framework::html.phtml';

    /**
     * @var string $handle
     */
    protected $handle;

    /**
     * @var \Awesome\Framework\Block\Html\Head $headTemplate
     */
    private $headTemplate;

    /**
     * Base Template constructor.
     */
    public function __construct()
    {
        $this->headTemplate = new \Awesome\Framework\Block\Html\Head();
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
     * Set current page head data.
     * @param array $headStructure
     * @return $this
     */
    public function setHeadStructure($headStructure)
    {
        $this->headTemplate->setHeadData($headStructure);

        return $this;
    }

    /**
     * Set current page body structure data.
     * @param array $bodyStructure
     * @return $this
     */
    public function setBodyStructure($bodyStructure)
    {
        $this->children = $bodyStructure['children'];

        return $this;
    }

    /**
     * Render head part of the page.
     * @return string
     */
    public function getHead()
    {
        $this->headTemplate->setView($this->view);

        return $this->headTemplate->toHtml();
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
}
