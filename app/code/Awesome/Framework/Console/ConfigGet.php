<?php
declare(strict_types=1);

namespace Awesome\Framework\Console;

use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Framework\Model\Config;

class ConfigGet extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * @var Config $config
     */
    private $config;

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
            ->addOption('encode', 'e', InputDefinition::OPTION_OPTIONAL, 'Allow encoding children structure if path is pointing to a section')
            ->addArgument('path', InputDefinition::ARGUMENT_REQUIRED, 'Config path to retrieve');
    }

    /**
     * Get config value.
     * @inheritDoc
     * @throws \Exception
     */
    public function execute(Input $input, Output $output): void
    {
        $path = $input->getArgument('path');

        if ($this->config->exists($path)) {
            $value = $this->config->get($path);

            if (is_array($value)) {
                if ($input->getOption('encode', true)) {
                    $output->writeln(array_export($value, true));
                } else {
                    $output->writeln('Use -e/--encode option to allow displaying children structure as an array.');

                    throw new \InvalidArgumentException('Provided path points to a configuration section');
                }
            } else {
                $output->writeln($value !== null ? (string) $value : $output->colourText('null', Output::BROWN));
            }
        } else {
            $output->writeln($output->colourText('(not set)', Output::RED));
        }
    }
}
