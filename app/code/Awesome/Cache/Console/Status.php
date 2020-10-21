<?php
declare(strict_types=1);

namespace Awesome\Cache\Console;

use Awesome\Cache\Model\Cache;
use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Framework\Model\Config;

class Status extends \Awesome\Console\Model\Cli\AbstractCommand
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
     * Cache Status constructor.
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
            ->setDescription('Show application cache status');
    }

    /**
     * Show application cache status.
     * @inheritDoc
     */
    public function execute(Input $input, Output $output): void
    {
        $types = $this->cache->getTypes();
        $padding = max(array_map(function ($type) {
            return strlen($type);
        }, $types));

        foreach ($types as $type) {
            $status = $this->config->get(Cache::CACHE_CONFIG_PATH . '/' . $type)
                ? $output->colourText('enabled')
                : $output->colourText('disabled', Output::BROWN);

            $output->writeln(str_pad($type, $padding + 2) . $status);
        }
    }
}
