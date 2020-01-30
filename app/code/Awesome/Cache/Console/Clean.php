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
    public function execute($output)
    {
        $this->cache->remove();
        //@TODO: Implement cache type argument

        $output->writeln('Cache was cleaned.');
    }
}
