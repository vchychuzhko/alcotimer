<?php
declare(strict_types=1);

namespace Vch\Cache\Console;

use Vch\Cache\Model\CacheState;
use Vch\Console\Model\Cli\Input;
use Vch\Console\Model\Cli\Input\InputDefinition;
use Vch\Console\Model\Cli\Output;

class Enable extends \Vch\Cache\Model\AbstractCacheCommand
{
    private CacheState $cacheState;

    /**
     * Cache Enable constructor.
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
            ->setDescription('Enable application cache')
            ->addArgument('types', InputDefinition::ARGUMENT_ARRAY, 'Cache types to be enabled');
    }

    /**
     * Enable application cache.
     * @inheritDoc
     */
    public function execute(Input $input, Output $output)
    {
        $types = $this->getTypes($input, $output);

        $output->writeln('Enabled cache types:');

        foreach ($types as $type) {
            $this->cacheState->enable($type);

            $output->writeln($type);
        }
    }
}
