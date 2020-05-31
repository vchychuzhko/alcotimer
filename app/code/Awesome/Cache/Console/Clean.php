<?php

namespace Awesome\Cache\Console;

use Awesome\Cache\Model\Cache;

class Clean extends \Awesome\Console\Model\Cli\AbstractCommand
{
    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * Clean constructor.
     */
    function __construct()
    {
        $this->cache = new Cache();
    }

    /**
     * @inheritDoc
     */
    public static function configure($definition)
    {
        return parent::configure($definition)
            ->setDescription('Flush application cache');
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
