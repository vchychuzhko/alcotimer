<?php

namespace Awesome\Base\Block\Html;

class Body extends \Awesome\Base\Block\Template
{
    protected $template = 'Awesome_Base::html/body.phtml';

    /**
     * @var array
     */
    private $structure;

    /**
     * Set page body structure.
     * @param array $structure
     * @return $this
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;

        return $this;
    }
}
