<?php

namespace Awesome\Cache\Console;

class Clean extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * @var \Awesome\Cache\Model\Cache $cache
     */
    private $cache;

    /**
     * Clean constructor.
     */
    function __construct()
    {
        $this->cache = new \Awesome\Cache\Model\Cache();
    }

    /**
     * Clean XML cache files.
     * @inheritDoc
     */
    public function execute($args = [])
    {
        $this->cache->remove();

        return 'Cache was cleaned.';
    }
}
