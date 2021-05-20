<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\FileManager;

use Awesome\Framework\Exception\XmlValidationException;
use Awesome\Framework\Model\Validator\XmlValidator;

class XmlFileManager extends \Awesome\Framework\Model\FileManager
{
    /**
     * @var XmlValidator $xmlValidator
     */
    private $xmlValidator;

    /**
     * XmlFileManager constructor.
     * @param XmlValidator $xmlValidator
     */
    public function __construct(XmlValidator $xmlValidator)
    {
        $this->xmlValidator = $xmlValidator;
    }

    /**
     * Read and parse XML file.
     * If XSD scheme file is provided, XML is checked for validity.
     * @param string $path
     * @param string|null $schemaFile
     * @return \SimpleXMLElement|false
     * @throws \Exception
     */
    public function parseXmlFile(string $path, ?string $schemaFile = null)
    {
        $content = $this->readFile($path);

        if (!$this->xmlValidator->valid($content)) {
            throw new XmlValidationException(__('Provided file "%1" does not contain valid XML', $path));
        }
        if ($schemaFile) {
            $schema = $this->readFile($schemaFile);

            if (!$this->xmlValidator->validAgainst($content, $schema)) {
                libxml_use_internal_errors(true);

                $xml = new \DOMDocument();
                $xml->load($path);

                if (!$xml->schemaValidate($schemaFile)) {
                    $error = libxml_get_last_error();

                    $errorMessage = trim($error->message);
                    libxml_clear_errors();

                    throw new XmlValidationException(__('%1 In file "%2"', $errorMessage, $path));
                }
            }
        }

        return simplexml_load_string($content);
    }
}
