<?php

namespace Awesome\Framework\Model\Validator;

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
    abstract public function valid($item);

    /**
     * Validate array of items.
     * @param array $items
     * @return bool
     */
    public function validItems($items)
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
    public function getInvalidItems()
    {
        return $this->invalid;
    }

    /**
     * Reset invalid items.
     * @return $this
     */
    public function reset()
    {
        $this->invalid = [];

        return  $this;
    }
}
