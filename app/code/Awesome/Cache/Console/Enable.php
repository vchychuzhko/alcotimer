<?php
declare(strict_types=1);

namespace Awesome\Cache\Console;

use Awesome\Cache\Model\CacheState;
use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;

class Enable extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * @var CacheState $cacheState
     */
    private $cacheState;

    /**
     * Cache Enable constructor.
     * @param CacheState $cacheState
     */
    public function __construct(CacheState $cacheState)
    {
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
    public function execute(Input $input, Output $output): void
    {
        $definedTypes = CacheState::getAllTypes();
        $types = $input->getArgument('types') ?: $definedTypes;
        $titleShown = false;

        foreach ($types as $type) {
            if (in_array($type, $definedTypes, true)) {
                $this->cacheState->enable($type);

                if (!$titleShown) {
                    $output->writeln('Enabled cache types:');
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
