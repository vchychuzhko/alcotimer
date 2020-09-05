<?php

namespace Awesome\Framework\Console;

use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Framework\Model\Config;

class ConfigSet extends \Awesome\Console\Model\Cli\AbstractCommand
{
    /**
     * @var Config $maintenance
     */
    private $config;

    /**
     * Config Set constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public static function configure($definition)
    {
        return parent::configure($definition)
            ->setDescription('Set configuration value by path')
            ->addArgument('path', InputDefinition::ARGUMENT_REQUIRED, 'Config path to update')
            ->addArgument('value', InputDefinition::ARGUMENT_REQUIRED, 'Config value to set');
    }

    /**
     * Set config value.
     * @inheritDoc
     * @throws \RuntimeException
     */
    public function execute($input, $output)
    {
        $path = $input->getArgument('path');
        $value = $input->getArgument('value', true);

        $this->config->set($path, $value);

        $output->writeln('Configuration was successfully updated.');
    }
}
