<?php
declare(strict_types=1);

namespace Awesome\Framework\Console;

use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Framework\Model\Config;

class ConfigSet extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * @var Config $config
     */
    private $config;

    /**
     * ConfigSet constructor.
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
            ->setDescription('Set configuration value by path')
            ->addOption('type', null, InputDefinition::OPTION_OPTIONAL, 'Value type to be casted, lowercase')
            ->addArgument('path', InputDefinition::ARGUMENT_REQUIRED, 'Config path to update')
            ->addArgument('value', InputDefinition::ARGUMENT_OPTIONAL, 'Config value to set. If omitted, considered as null');
    }

    /**
     * Set config value.
     * @inheritDoc
     * @throws \RuntimeException
     */
    public function execute(Input $input, Output $output): void
    {
        $path = $input->getArgument('path');

        if ($type = $input->getOption('type')) {
            if (!in_array($type, ['int', 'integer', 'float', 'double', 'bool', 'boolean', 'string'], true)) {
                throw new \InvalidArgumentException(sprintf('Provided value type "%s" is not valid', $type));
            }
            $value = $input->getArgument('value');
            settype($value, $type);
        } else {
            $value = $input->getArgument('value', true);
        }

        if ($this->config->set($path, $value)) {
            $output->writeln('Configuration was successfully updated.');
        } else {
            $output->writeln('Configuration was not updated. Please, check the provided config path.');

            throw new \InvalidArgumentException('Configuration was not updated');
        }
    }
}
