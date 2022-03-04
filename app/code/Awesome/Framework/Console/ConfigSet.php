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
            ->addOption('create', 'c', InputDefinition::OPTION_OPTIONAL, 'Allow creating new record if configuration is not yet present')
            ->addOption('type', null, InputDefinition::OPTION_OPTIONAL, 'Type for value to be casted to, in lowercase')
            ->addArgument('path', InputDefinition::ARGUMENT_REQUIRED, 'Config path to update')
            ->addArgument('value', InputDefinition::ARGUMENT_OPTIONAL, 'Config value to set. If omitted, considered as null');
    }

    /**
     * Set config value.
     * @inheritDoc
     * @throws \Exception
     */
    public function execute(Input $input, Output $output)
    {
        // @TODO: Add unset config command/method or add -r/--remove option
        if ($type = $input->getOption('type')) {
            if (!in_array($type, ['int', 'integer', 'float', 'double', 'bool', 'boolean', 'string'], true)) {
                throw new \InvalidArgumentException(__('Provided value type "%1" is not valid', $type));
            }
            $value = $input->getArgument('value');
            settype($value, $type);
        } else {
            $value = $input->getArgument('value');
        }
        $path = $input->getArgument('path');

        if (!$this->config->exists($path) && !$input->getOption('create')) {
            $output->writeln('Use -c/--create option to allow creating new configuration record.');

            throw new \InvalidArgumentException(__('Provided path is not yet registered'));
        }

        if (is_array($this->config->get($path))) {
            throw new \InvalidArgumentException(__('Provided path points to configuration section and cannot be updated via CLI'));
        }

        $this->config->set($path, $value);

        $output->writeln('Configuration was successfully updated.');
    }
}
