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
    protected $head;

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
     * Set current page head data handle.
     * @param string $head
     * @return $this
     */
    public function setHead($head)
    {
        $this->head = $head;

        return $this;
    }

    /**
     * Create and render head part of the page.
     * @return string
     */
    public function getHead()
    {
        $head = '';

        if ($headStructure = $this->head ?? []) {
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
}
