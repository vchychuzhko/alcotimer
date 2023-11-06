<?php
declare(strict_types=1);

namespace Awesome\Cache\Console;

use Awesome\Cache\Model\CacheState;
use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;

class Disable extends \Awesome\Cache\Model\AbstractCacheCommand
{
    private CacheState $cacheState;

    /**
     * Cache Disable constructor.
     * @param CacheState $cacheState
     */
    public function __construct(
        CacheState $cacheState
    ) {
        $this->cacheState = $cacheState;
    }

    /**
     * @inheritDoc
     */
    public static function configure(): InputDefinition
    {
        return parent::configure()
            ->setDescription('Disable application cache')
            ->addArgument('types', InputDefinition::ARGUMENT_ARRAY, 'Cache types to be disabled');
    }

    /**
     * Disable application cache.
     * @inheritDoc
     */
    public function execute(Input $input, Output $output)
    {
        $types = $this->getTypes($input, $output);

        $output->writeln('Disabled cache types:');

        foreach ($types as $type) {
            $this->cacheState->disable($type);

            $output->writeln($type);
        }
    }
}
