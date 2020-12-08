<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block;

/**
 * Class Container
 * @method string|null getHtmlTag()
 * @method string|null getHtmlClass()
 * @method string|null getHtmlId()
 */
class Container extends \Awesome\Frontend\Model\AbstractBlock
{
    /**
     * Render container element according to provided html node data.
     * @inheritDoc
     */
    public function toHtml(): string
    {
        $html = $this->getChildHtml();

        if ($tag = $this->getHtmlTag()) {
            $class = $this->getHtmlClass() ? ' class="' . $this->getHtmlClass() . '"' : '';
            $id = $this->getHtmlId() ? ' id="' . $this->getHtmlId() . '"' : '';

            $html = <<<HTML
<{$tag}{$class}{$id}>$html</$tag>
HTML;
        }

        return $html;
    }
}
