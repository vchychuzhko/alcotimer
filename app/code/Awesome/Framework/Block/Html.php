<?php

namespace Awesome\Framework\Block;

class Html extends \Awesome\Framework\Block\Template
{
    protected $template = 'Awesome_Framework::html.phtml';

    /**
     * @var string $handle
     */
    protected $handle;

    /**
     * @var string $head
     */
    protected $headStructure;

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
     * @param string $headStructure
     * @return $this
     */
    public function setHeadStructure($headStructure)
    {
        $this->headStructure = $headStructure;

        return $this;
    }

    /**
     * Render head part of the page.
     * @return string
     */
    public function getHead()
    {
        $headHtml = '';

        if ($headStructure = $this->headStructure ?? []) {
            $this->headTemplate->setView($this->view)
                ->setStructure($headStructure);

            $headHtml = $this->headTemplate->toHtml();
        }

        return $headHtml;
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
