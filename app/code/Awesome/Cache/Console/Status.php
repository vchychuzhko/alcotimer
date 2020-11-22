<?php
declare(strict_types=1);

namespace Awesome\Cache\Console;

use Awesome\Cache\Model\Cache;
use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Framework\Helper\DataHelper;

class Status extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * Cache Status constructor.
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public static function configure(): InputDefinition
    {
        return parent::configure()
            ->setDescription('Show application cache status');
    }

    /**
     * Show application cache status.
     * @inheritDoc
     */
    public function execute(Input $input, Output $output): void
    {
        $types = $this->cache->getTypes();
        $padding = DataHelper::getMaxLength($types);

        foreach ($types as $type) {
            $status = $this->cache->cacheTypeEnabled($type)
                ? $output->colourText('enabled')
                : $output->colourText('disabled', Output::BROWN);

            $output->writeln(str_pad($type, $padding + 2) . $status);
        }
    }
}
