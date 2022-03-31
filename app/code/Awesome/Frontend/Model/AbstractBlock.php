<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Frontend\Model\Layout;

abstract class AbstractBlock extends \Awesome\Framework\Model\DataObject implements \Awesome\Frontend\Model\BlockInterface
{
    protected Layout $layout;

    protected string $nameInLayout;

    protected ?string $template = null;

    /**
     * AbstractBlock constructor.
     * @param Layout $layout
     * @param string $nameInLayout
     * @param string|null $template
     * @param array $data
     */
    public function __construct(
        Layout $layout,
        string $nameInLayout,
        ?string $template = null,
        array $data = []
    ) {
        parent::__construct($data, true);
        $this->layout = $layout;
        $this->nameInLayout = $nameInLayout;
        $this->template = $template ?: $this->template;
    }

    /**
     * @inheritDoc
     */
    public function toHtml(): string
    {
        return $this->layout->renderElement($this);
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

        if ($childName) {
            $childNames = $this->layout->getChildNames($this->getNameInLayout(), true);

            if (in_array($childName, $childNames, true)) {
                $html = $this->layout->render($childName);
            }
        } else {
            $childNames = $this->layout->getChildNames($this->getNameInLayout());

            foreach ($childNames as $child) {
                $html .= $this->layout->render($child);
            }
        }

        return $html;
    }

    /**
     * Get element name.
     * @return string
     */
    public function getNameInLayout(): string
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
}
