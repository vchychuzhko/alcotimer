<?php

namespace Awesome\Framework\Model;

use Awesome\Cache\Model\Cache;

abstract class AbstractHandler
{
    /**
     * @var Cache $cache
     */
    protected $cache;

    /**
     * AbstractHandler constructor.
     */
    function __construct()
    {
        $this->cache = new Cache();
    }

    /**
     * Process requested handle.
     * @param string $handle
     * @return string
     */
    abstract public function process($handle);

    /**
     * Check if requested handle exists.
     * @param string $handle
     * @return bool
     */
    abstract public function exist($handle);

    /**
     * Parse requested handle.
     * @param string $handle
     * @return string
     */
    abstract public function parse($handle);
}
