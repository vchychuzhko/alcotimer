<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Frontend\Model\Layout;

abstract class AbstractBlock extends \Awesome\Framework\Model\DataObject implements \Awesome\Frontend\Model\BlockInterface
{
    protected Layout $layout;

    protected string $nameInLayout;

    protected $template;

    /**
     * AbstractBlock constructor.
     * @param array $data
     */
    public function __construct(
        array $data = []
    ) {
        parent::__construct($data, true);
    }

    /**
     * Initialize block fields.
     * @param Layout $layout
     * @param string $nameInLayout
     * @param string|null $template
     */
    public function init(
        Layout $layout,
        string $nameInLayout = '',
        ?string $template = null
    ) {
        $this->layout = $layout;
        $this->nameInLayout = $nameInLayout;
        $this->template = $template ?: $this->template;
    }

    /**
     * @inheritDoc
     */
    public function toHtml(): string
    {
        $html = '';

        if ($layout = $this->getLayout()) {
            $html = $layout->renderElement($this);
        }

        return $html;
    }

    /**
     * Get child element content.
     * Render all children if no name is specified.
     * @param string $childName
     * @return string
     */
    public function getChildHtml(string $childName = ''): string
    {
        $html = '';

        if ($layout = $this->getLayout()) {
            if ($childName) {
                $childNames = $layout->getChildNames($this->getNameInLayout(), true);

                if (in_array($childName, $childNames, true)) {
                    $html = $layout->render($childName);
                }
            } else {
                $childNames = $layout->getChildNames($this->getNameInLayout());

                foreach ($childNames as $child) {
                    $html .= $layout->render($child);
                }
            }
        }

        return $html;
    }

    /**
     * Get element name.
     * @return string|null
     */
    public function getNameInLayout(): ?string
    {
        return $this->nameInLayout;
    }

    /**
     * Get element template.
     * @return string|null
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * Get page layout.
     * @return Layout|null
     */
    protected function getLayout(): ?Layout
    {
        return $this->layout;
    }
}
