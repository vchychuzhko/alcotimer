<?php

namespace Awesome\Base\Model;

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
     * Check if string is a boolean and convert it.
     * @param string $value
     * @return string|bool
     */
    protected function stringBooleanCheck($value)
    {
        return ($value === 'true' || $value === 'false')
            ? ($value === 'true')
            : $value;
    }
}
