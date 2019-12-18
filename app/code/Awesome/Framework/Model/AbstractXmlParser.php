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
     * @param string $value
     * @return bool
     */
    protected function stringBooleanCheck($value)
    {
        return $value === 'true';
    }
}
