<?php
declare(strict_types=1);

namespace Awesome\Cache\Console;

use Awesome\Cache\Model\Cache;
use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;

class Clean extends \Awesome\Cache\Model\AbstractCacheCommand
{
    private Cache $cache;

    /**
     * Cache Clean constructor.
     * @param Cache $cache
     */
    public function __construct(
        Cache $cache
    ) {
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public static function configure(): InputDefinition
    {
        return parent::configure()
            ->setDescription('Clear application cache')
            ->addArgument('types', InputDefinition::ARGUMENT_ARRAY, 'Cache types to be cleared');
    }

    /**
     * Clear application cache.
     * @inheritDoc
     */
    public function execute(Input $input, Output $output)
    {
        $types = $this->getTypes($input, $output);

        $output->writeln('Cleared cache types:');

        foreach ($types as $type) {
            $this->cache->invalidate($type);

            $output->writeln($type);
        }
    }
}
