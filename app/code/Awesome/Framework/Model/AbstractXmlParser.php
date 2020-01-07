<?php

namespace Awesome\Framework\Model;

abstract class AbstractXmlParser
{
    /**
     * @var \Awesome\Cache\Model\Cache $cache
     */
    protected $cache;

    /**
     * XmlParser constructor.
     */
    function __construct()
    {
        $this->cache = new \Awesome\Cache\Model\Cache();
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
