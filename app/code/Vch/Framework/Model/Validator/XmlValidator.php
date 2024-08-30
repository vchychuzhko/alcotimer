<?php
declare(strict_types=1);

namespace Vch\Framework\Model\Validator;

class XmlValidator implements \Vch\Framework\Model\ValidatorInterface
{
    /**
     * Validate XML string.
     * @param string $item
     * @return bool
     */
    public function valid($item): bool
    {
        return @simplexml_load_string($item) !== false;
    }
}
