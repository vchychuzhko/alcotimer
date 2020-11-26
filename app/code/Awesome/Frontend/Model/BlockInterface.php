<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Frontend\Model\Layout;

interface BlockInterface
{
    /**
     * Initialize block fields.
     * @param Layout $layout
     * @param string $nameInLayout
     * @param string|null $template
     * @return void
     */
    public function init(Layout $layout, string $nameInLayout = '', ?string $template = null): void;

    /**
     * Render block content.
     * @return string
     */
    public function toHtml(): string;

    /**
     * Get child element content.
     * Render all children if no name is specified.
     * @param string $childName
     * @return string
     */
    public function getChildHtml(string $childName = ''): string;

    /**
     * Get element name.
     * @return string
     */
    public function getNameInLayout(): string;

    /**
     * Get element template.
     * @return string|null
     */
    public function getTemplate(): ?string;

    /**
     * Get page layout if already set.
     * @return Layout|null
     */
    public function getLayout(): ?Layout;
}
