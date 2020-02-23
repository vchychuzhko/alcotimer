<?php

namespace Awesome\Cache\Console;

class Clean extends \Awesome\Framework\Model\Cli\AbstractCommand
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
    public function execute($input, $output)
    {
        $this->cache->remove();
        //@TODO: Add cache type argument

        $output->writeln('Cache was cleaned.');
    }
}
