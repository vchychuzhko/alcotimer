<?php
declare(strict_types=1);

namespace Vch\Framework\Console;

use Vch\Console\Model\Cli\Input;
use Vch\Console\Model\Cli\Input\InputDefinition;
use Vch\Console\Model\Cli\Output;
use Vch\Framework\Model\Config;

class ConfigGet extends \Vch\Console\Model\AbstractCommand
{
    private Config $config;

    /**
     * ConfigGet constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public static function configure(): InputDefinition
    {
        return parent::configure()
            ->setDescription('Get configuration value by path')
            ->addArgument('path', InputDefinition::ARGUMENT_REQUIRED, 'Config path to retrieve');
    }

    /**
     * Get config value.
     * @inheritDoc
     * @throws \Exception
     */
    public function execute(Input $input, Output $output)
    {
        $path = $input->getArgument('path');

        $value = $this->config->get($path);

        if ($value !== null) {
            if (is_array($value)) {
                $output->writeln(array_export($value, true));
            } else {
                $output->writeln((string) $value);
            }
        } else {
            $output->writeln($output->colourText('(not set)', Output::RED));
        }
    }
}
