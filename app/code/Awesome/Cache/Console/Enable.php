<?php
declare(strict_types=1);

namespace Awesome\Cache\Console;

use Awesome\Cache\Model\Cache;
use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Framework\Model\Config;

class Enable extends \Awesome\Console\Model\Cli\AbstractCommand
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
     * Cache Enable constructor.
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
    public static function configure(InputDefinition $definition): InputDefinition
    {
        return parent::configure($definition)
            ->setDescription('Enable application cache')
            ->addArgument('types', InputDefinition::ARGUMENT_ARRAY, 'Cache types to be enabled');
    }

    /**
     * Enable application cache.
     * @inheritDoc
     */
    public function execute(Input $input, Output $output): void
    {
        $definedTypes = $this->cache->getTypes();
        $types = $input->getArgument('types') ?: $definedTypes;

        foreach ($types as $type) {
            if (in_array($type, $definedTypes, true)) {
                $this->config->set(Cache::CACHE_CONFIG_PATH . '/' . $type, 1);
                $output->writeln('Cache enabled: ' . $type);
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
