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

    /**
     * Validate XML string against provided XSD schema.
     * @param string $item
     * @param string $schema
     * @return bool
     */
    public function validAgainst(string $item, string $schema): bool
    {
        $valid = true;
        libxml_use_internal_errors(true);

        $xml = new \DOMDocument();
        $xml->loadXML($item);

        if (!$xml->schemaValidateSource($schema)) {
            $valid = false;
            libxml_clear_errors();
        }

        return $valid;
    }
}
