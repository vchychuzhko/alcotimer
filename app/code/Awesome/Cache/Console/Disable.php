<?php

namespace Awesome\Cache\Console;

use Awesome\Cache\Model\Cache;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Framework\Model\Config;

class Disable extends \Awesome\Console\Model\Cli\AbstractCommand
{
    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var Config $config
     */
    private $config;

    /**
     * Cache Disable constructor.
     * @param Cache $cache
     * @param Config $config
     */
    public function __construct(Cache $cache, Config $config)
    {
        $this->cache = $cache;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public static function configure($definition)
    {
        return parent::configure($definition)
            ->setDescription('Disable application cache')
            ->addArgument('types', InputDefinition::ARGUMENT_ARRAY, 'Cache types to be disabled');
    }

    /**
     * Disable application cache.
     * @inheritDoc
     */
    public function execute($input, $output)
    {
        $definedTypes = $this->cache->getTypes();
        $types = $input->getArgument('types') ?: $definedTypes;

        foreach ($types as $type) {
            if (in_array($type, $definedTypes, true)) {
                $this->config->set(Cache::CACHE_CONFIG_PATH . '/' . $type, 0);
                $output->writeln('Cache disabled: ' . $type);
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
