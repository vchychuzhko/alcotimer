<?php
declare(strict_types=1);

namespace Awesome\Cache\Console;

use Awesome\Cache\Model\CacheState;
use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Framework\Helper\DataHelper;

class Status extends \Awesome\Cache\Model\AbstractCacheCommand
{
    private CacheState $cacheState;

    /**
     * Cache Status constructor.
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
            ->setDescription('Show application cache status')
            ->addArgument('types', InputDefinition::ARGUMENT_ARRAY, 'Cache types to show status about');
    }

    /**
     * Show application cache status.
     * @inheritDoc
     */
    public function execute(Input $input, Output $output)
    {
        $types = $this->getTypes($input, $output);
        $padding = DataHelper::getMaxLength($types) + 2;

        $output->writeln('Cache types statuses:');

        foreach ($types as $type) {
            $status = $this->cacheState->isEnabled($type)
                ? $output->colourText('enabled')
                : $output->colourText('disabled', Output::BROWN);

            $output->writeln(str_pad($type, $padding) . $status);
        }
    }
}
