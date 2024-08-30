<?php
declare(strict_types=1);

namespace Vch\Framework\Model;

use Vch\Framework\Helper\DataHelper;
use Vch\Framework\Model\FileManager\XmlFileManager;

abstract class AbstractXmlParser
{
    protected XmlFileManager $xmlFileManager;

    /**
     * AbstractXmlParser constructor.
     * @param XmlFileManager $xmlFileManager
     */
    public function __construct(
        XmlFileManager $xmlFileManager
    ) {
        $this->xmlFileManager = $xmlFileManager;
    }

    /**
     * Check if parsed XML node is marked as disabled.
     * @param array $parsedNode
     * @return bool
     */
    protected function isDisabled(array $parsedNode): bool
    {
        return isset($parsedNode['disabled']) && DataHelper::isStringBooleanTrue($parsedNode['disabled']);
    }
}
