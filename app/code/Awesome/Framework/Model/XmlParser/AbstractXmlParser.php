<?php

namespace Awesome\Framework\Model\XmlParser;

use Awesome\Cache\Model\Cache;

abstract class AbstractXmlParser
{
    /**
     * @var Cache $cache
     */
    protected $cache;

    /**
     * XmlParser constructor.
     */
    function __construct()
    {
        $this->cache = new Cache();
    }

    /**
     * Check if string is a boolean "true", otherwise return false.
     * Not case sensitive.
     * @param string $string
     * @return bool
     */
    protected function stringBooleanCheck($string)
    {
        return strtolower($string) === 'true';
    }
}
