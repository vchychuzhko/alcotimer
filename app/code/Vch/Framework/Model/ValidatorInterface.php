<?php
declare(strict_types=1);

namespace Vch\Framework\Model;

interface ValidatorInterface
{
    /**
     * Validate provided item.
     * @param mixed $item
     * @return bool
     */
    public function valid($item): bool;
}
