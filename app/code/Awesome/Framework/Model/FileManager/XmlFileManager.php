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
     * @param string $path
     * @return \SimpleXMLElement|false
     * @throws \Exception
     */
    public function parseXmlFile(string $path)
    {
        $content = $this->readFile($path);

        if (!$this->xmlValidator->valid($content)) {
            throw new XmlValidationException(sprintf('Provided file "%s" does not contain valid XML', $path));
        }

        return simplexml_load_string($content);
    }
}
