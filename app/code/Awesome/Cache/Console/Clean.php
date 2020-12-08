<?php
declare(strict_types=1);

namespace Awesome\Cache\Console;

use Awesome\Cache\Model\Cache;
use Awesome\Cache\Model\CacheState;
use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;

class Clean extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var CacheState $cacheState
     */
    private $cacheState;

    /**
     * Cache Clean constructor.
     * @param Cache $cache
     * @param CacheState $cacheState
     */
    public function __construct(Cache $cache, CacheState $cacheState)
    {
        $this->cache = $cache;
        $this->cacheState = $cacheState;
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
    public function execute(Input $input, Output $output): void
    {
        $definedTypes = $this->cacheState->getDefinedTypes();
        $types = $input->getArgument('types') ?: $definedTypes;
        $titleShown = false;

        foreach ($types as $type) {
            if (in_array($type, $definedTypes, true)) {
                $this->cache->invalidate($type);

                if (!$titleShown) {
                    $output->writeln('Cleared cache types:');
                    $titleShown = true;
                }
                $output->writeln($type);
            } else {
                $output->writeln('Provided cache type was not recognized.');
                $output->writeln();
                $output->writeln('Allowed types:');
                $output->writeln($output->colourText(implode(', ', $definedTypes)), 2);

                throw new \InvalidArgumentException('Invalid cache type is provided');
            }
        }
    }
}
