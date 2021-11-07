<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Validator;

class JsonValidator implements \Awesome\Framework\Model\ValidatorInterface
{
    /**
     * Validate JSON string.
     * @param string $item
     * @return bool
     */
    public function valid($item): bool
    {
        return @json_decode($item, true) !== null;
    }
}
