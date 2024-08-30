<?php
declare(strict_types=1);

namespace Vch\Framework\Model\Validator;

class JsonValidator implements \Vch\Framework\Model\ValidatorInterface
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
