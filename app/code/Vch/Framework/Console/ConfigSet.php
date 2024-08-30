<?php
declare(strict_types=1);

namespace Vch\Framework\Console;

use Vch\Console\Model\Cli\Input;
use Vch\Console\Model\Cli\Input\InputDefinition;
use Vch\Console\Model\Cli\Output;
use Vch\Framework\Model\Config;

class ConfigSet extends \Vch\Console\Model\AbstractCommand
{
    private Config $config;

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
            ->addArgument('value', InputDefinition::ARGUMENT_REQUIRED, 'Config value to set');
    }

    /**
     * Set config value.
     * @inheritDoc
     * @throws \Exception
     */
    public function execute(Input $input, Output $output)
    {
        $path = $input->getArgument('path');
        $value = $input->getArgument('value');

        if ($type = $input->getOption('type')) {
            if (!in_array($type, ['int', 'float', 'bool', 'string'], true)) {
                throw new \InvalidArgumentException(__('Provided value type "%1" is not valid', $type));
            }
            settype($value, $type);
        }

        $prevValue = $this->config->get($path);

        if ($prevValue === null && !$input->getOption('create')) {
            $output->writeln('Use -c/--create option to allow creating new configuration record.');

            throw new \InvalidArgumentException(__('Provided configuration path is not yet registered'));
        }

        if (is_array($prevValue)) {
            $output->writeln('Section should be updated by each field separately.');

            throw new \InvalidArgumentException(__('Provided configuration path points to a section'));
        }

        $this->config->set($path, $value);

        $output->writeln('Configuration was successfully updated!');
    }
}
