<?php

namespace Awesome\Framework\Block\Template;

class Container extends \Awesome\Framework\Block\Template
{
    /**
     * @inheritDoc
     */
    protected $template = 'Awesome_Framework::template/container.phtml';

    /**
     * @var string $htmlTag
     */
    protected $htmlTag;

    /**
     * @var string $htmlClass
     */
    protected $htmlClass;

    /**
     * @var string $htmlId
     */
    protected $htmlId;

    /**
     * Set html tag data.
     * @param array $containerData
     * @return $this
     */
    public function setContainerTagData($containerData)
    {
        $this->htmlTag = $containerData['htmlTag'];
        $this->htmlClass = $containerData['htmlClass'] ?? '';
        $this->htmlId = $containerData['htmlId'] ?? '';

        return $this;
    }

    /**
     * Get container element tag name.
     * @return string
     */
    public function getHtmlTag()
    {
        return $this->htmlTag ?: '';
    }

    /**
     * Get container element class.
     * @return string
     */
    public function getHtmlClass()
    {
        return $this->htmlClass ?: '';
    }

    /**
     * Get container element id.
     * @return string
     */
    public function getHtmlId()
    {
        return $this->htmlId ?: '';
    }
}
