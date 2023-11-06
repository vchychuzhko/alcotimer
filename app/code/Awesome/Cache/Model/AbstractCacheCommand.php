<?php
declare(strict_types=1);

namespace Awesome\Cache\Model;

use Awesome\Cache\Model\Cache;
use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Output;

abstract class AbstractCacheCommand extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * Get command types argument.
     * Perform type check and return defined types in case none is provided.
     * @param Input $input
     * @param Output $output
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function getTypes(Input $input, Output $output): array
    {
        $definedTypes = Cache::getAllTypes();
        $types = $input->getArgument('types') ?: $definedTypes;

        $notRecognizedTypes = array_diff($types, $definedTypes);

        if ($notRecognizedTypes) {
            $output->writeln('Provided cache types were not recognized: ' . $output->colourText(implode(', ', $notRecognizedTypes), Output::BROWN));
            $output->writeln();
            $output->writeln('Allowed types:');
            $output->writeln($output->colourText(implode(', ', $definedTypes)), 2);

            throw new \InvalidArgumentException('Invalid cache type is provided');
        }

        return $types;
    }
}
