<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\FileManager;

use Awesome\Framework\Exception\FileSystemException;
use Awesome\Framework\Exception\XmlValidationException;

class XmlFileManager extends \Awesome\Framework\Model\FileManager
{
    /**
     * Read and parse XML file.
     * If XSD scheme file is provided, XML is checked for validity.
     * @param string $path
     * @param string|null $schemaFile
     * @return \SimpleXMLElement
     * @throws FileSystemException
     * @throws XmlValidationException
     * @deprecated see Next version of this method
     */
    public function parseXmlFile(string $path, ?string $schemaFile = null): \SimpleXMLElement
    {
        $content = $this->readFile($path);

        if (!$xmlNode = @simplexml_load_string($content)) {
            throw new XmlValidationException(__('Provided file "%1" does not contain valid XML', $path));
        }

        if ($schemaFile) {
            if (!file_exists($schemaFile)) {
                throw new FileSystemException(__('Provided schema path "%1" does not exist', $schemaFile));
            }
            if (!is_file($schemaFile)) {
                throw new FileSystemException(__('Provided schema path "%1" is not a file and cannot be used', $schemaFile));
            }

            libxml_use_internal_errors(true);

            $xml = new \DOMDocument();
            $xml->loadXML($content);

            if (!$xml->schemaValidate($schemaFile)) {
                $error = libxml_get_last_error();

                $errorMessage = trim($error->message);
                libxml_clear_errors();

                throw new XmlValidationException(__('%1 File: %2', $errorMessage, $path));
            }
        }

        return $xmlNode;
    }

    /**
     * Read and parse XML file.
     * If XSD scheme file is provided, XML is checked for validity.
     * @param string $path
     * @param string|null $schemaFile
     * @return array
     * @throws FileSystemException
     * @throws XmlValidationException
     */
    public function parseXmlFileNext(string $path, ?string $schemaFile = null): array
    {
        return $this->parse($this->parseXmlFile($path, $schemaFile));
    }

    /**
     * Parse XML node into array.
     * @param \SimpleXMLElement $node
     * @return array
     */
    private function parse(\SimpleXMLElement $node): array
    {
        $parsedNode = [];

        foreach ($node->attributes() as $attribute => $value) {
            $parsedNode[$attribute] = (string) $value;
        }

        foreach ($node->children() as $type => $child) {
            $parsedNode['_' . $type][] = $this->parse($child);
        }

        return $parsedNode;
    }
}
