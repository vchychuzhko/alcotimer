<?php

namespace Awesome\Maintenance\Console;

use Awesome\Framework\Model\Cli\Input\InputDefinition;
use Awesome\Maintenance\Model\Maintenance;
use Awesome\Framework\Validator\IpValidator;

class Enable extends \Awesome\Framework\Model\Cli\AbstractCommand
{
    /**
     * @var Maintenance $maintenance
     */
    private $maintenance;

    /**
     * @var IpValidator $validator
     */
    private $validator;

    /**
     * Maintenance Enable constructor.
     */
    public function __construct()
    {
        $this->maintenance = new Maintenance();
        $this->validator = new IpValidator();
    }

    /**
     * @inheritDoc
     */
    public static function configure($definition)
    {
        return parent::configure($definition)
            ->setDescription('Enable maintenance mode with list of allowed ids.')
            ->addOption('force', 'f', InputDefinition::OPTION_OPTIONAL, 'Force maintenance mode enabling')
            ->addArgument('ips', InputDefinition::ARGUMENT_ARRAY, 'List of IP addresses to exclude');
    }

    /**
     * Enable maintenance mode.
     * @inheritDoc
     */
    public function execute($input, $output)
    {
        $allowedIPs = $input->getArgument('ips');

        if ($this->validator->validItems($allowedIPs) || $input->getOption('force')) {
            $this->maintenance->enable($allowedIPs);

            $output->writeln('Maintenance mode was enabled.');
        } else {
            $output->write('Provided IP addresses are not valid, please, check them and try again: ');
            $output->writeln($output->colourText(implode(', ', $this->validator->getInvalidItems()), 'brown'));
            $output->writeln('Use -f/--force option if you want to proceed anyway.');
        }
    }
}
