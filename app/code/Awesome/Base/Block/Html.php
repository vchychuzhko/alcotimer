<?php

namespace Awesome\Base\Block;

class Html extends \Awesome\Base\Block\Template
{
    protected $template = 'Awesome_Base::html.phtml';

    /**
     * @var array $structure
     */
    protected $structure;

    /**
     * @var \Awesome\Base\Block\Html\Head $headTemplate
     */
    private $headTemplate;

    /**
     * @var \Awesome\Base\Block\Html\Body $bodyTemplate
     */
    private $bodyTemplate;

    /**
     * Base Template constructor.
     */
    public function __construct()
    {
        $this->headTemplate = new \Awesome\Base\Block\Html\Head();
        $this->bodyTemplate = new \Awesome\Base\Block\Html\Body();
        parent::__construct();
    }

    /**
     *
     * @param $structure
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
            $this->headTemplate->setData($headStructure);
            $this->headTemplate->setPageData($this->handle, $this->view);
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

        if ($bodyStructure = $this->structure['head']) {
            $this->bodyTemplate->setData($bodyStructure);
            $this->bodyTemplate->setPageData($this->handle, $this->view);
            $body = $this->bodyTemplate->toHtml();
        };

        return $body;
    }
}
