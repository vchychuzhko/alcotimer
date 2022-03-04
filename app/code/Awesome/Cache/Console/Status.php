<?php
declare(strict_types=1);

namespace Awesome\Cache\Console;

use Awesome\Cache\Model\CacheState;
use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Framework\Helper\DataHelper;

class Status extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * @var CacheState $cacheState
     */
    private $cacheState;

    /**
     * Cache Status constructor.
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
            ->setDescription('Show application cache status')
            ->addArgument('types', InputDefinition::ARGUMENT_ARRAY, 'Cache types to show status about');
    }

    /**
     * Show application cache status.
     * @inheritDoc
     */
    public function execute(Input $input, Output $output)
    {
        $definedTypes = CacheState::getAllTypes();
        $types = $input->getArgument('types') ?: $definedTypes;
        $titleShown = false;
        $padding = DataHelper::getMaxLength($types) + 2;

        foreach ($types as $type) {
            if (in_array($type, $definedTypes, true)) {
                $status = $this->cacheState->isEnabled($type)
                    ? $output->colourText('enabled')
                    : $output->colourText('disabled', Output::BROWN);

                if (!$titleShown) {
                    $output->writeln('Cache types statuses:');
                    $titleShown = true;
                }

                $output->writeln(str_pad($type, $padding) . $status);
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
