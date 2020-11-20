<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Validator;

class XmlValidator implements \Awesome\Framework\Model\ValidatorInterface
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
