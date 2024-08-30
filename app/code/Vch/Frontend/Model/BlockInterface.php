<?php
declare(strict_types=1);

namespace Vch\Frontend\Model;

interface BlockInterface
{
    /**
     * Render block content.
     * @return string
     */
    public function toHtml(): string;
}
