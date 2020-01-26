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
     * @inheritDoc
     */
    function __construct($options = [], $arguments = [])
    {
        $this->cache = new \Awesome\Cache\Model\Cache();
        parent::__construct($options, $arguments);
    }

    /**
     * Clean XML cache files.
     * @inheritDoc
     */
    public function execute()
    {
        $this->cache->remove();
        //@TODO: Implement cache type argument

        return 'Cache was cleaned.';
    }
}
