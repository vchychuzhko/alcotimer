<?php
declare(strict_types=1);

namespace Vch\Cache\Console;

use Vch\Cache\Model\CacheState;
use Vch\Console\Model\Cli\Input;
use Vch\Console\Model\Cli\Input\InputDefinition;
use Vch\Console\Model\Cli\Output;

class Disable extends \Vch\Cache\Model\AbstractCacheCommand
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
