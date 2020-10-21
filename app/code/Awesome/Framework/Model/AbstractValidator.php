<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

abstract class AbstractValidator
{
    /**
     * @var array $invalid
     */
    protected $invalid = [];

    /**
     * Validate provided item.
     * @param mixed $item
     * @return bool
     */
    abstract public function valid($item): bool;

    /**
     * Validate array of items.
     * @param array $items
     * @return bool
     */
    public function validItems(array $items): bool
    {
        $valid = true;

        foreach ($items as $item) {
            $valid = $this->valid($item) && $valid;
        }

        return $valid;
    }

    /**
     * Get last invalid item.
     * @return mixed
     */
    public function getFirstInvalidItem()
    {
        return reset($this->invalid);
    }

    /**
     * Get last invalid item.
     * @return mixed
     */
    public function getLastInvalidItem()
    {
        return end($this->invalid);
    }

    /**
     * Get all invalid items.
     * @return array
     */
    public function getInvalidItems(): array
    {
        return $this->invalid;
    }

    /**
     * Remove stored invalid items.
     * @return $this
     */
    public function reset(): self
    {
        $this->invalid = [];

        return  $this;
    }
}
