<?php
declare(strict_types=1);

namespace Awesome\Framework\Console;

use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Framework\Model\Maintenance;
use Awesome\Framework\Model\Validator\IpValidator;

class MaintenanceEnable extends \Awesome\Console\Model\Cli\AbstractCommand
{
    /**
     * @var Maintenance $maintenance
     */
    private $maintenance;

    /**
     * @var IpValidator $ipValidator
     */
    private $ipValidator;

    /**
     * Maintenance Enable constructor.
     * @param Maintenance $maintenance
     * @param IpValidator $ipValidator
     */
    public function __construct(Maintenance $maintenance, IpValidator $ipValidator)
    {
        $this->maintenance = $maintenance;
        $this->ipValidator = $ipValidator;
    }

    /**
     * @inheritDoc
     */
    public static function configure(): InputDefinition
    {
        return parent::configure()
            ->setDescription('Enable maintenance mode with a list of allowed IPs')
            ->addOption('force', 'f', InputDefinition::OPTION_OPTIONAL, 'Skip IP validation')
            ->addArgument('ips', InputDefinition::ARGUMENT_ARRAY, 'List of IP addresses to exclude');
    }

    /**
     * Enable maintenance mode.
     * @inheritDoc
     * @throws \InvalidArgumentException
     */
    public function execute(Input $input, Output $output): void
    {
        $allowedIPs = $input->getArgument('ips') ?: [];

        if ($allowedIPs && !$input->getOption('force')) {
            foreach ($allowedIPs as $allowedIP) {
                if (!$this->ipValidator->valid($allowedIP)) {
                    $output->write('Provided IP address is not valid, please, check it and try again: ');
                    $output->writeln($output->colourText($allowedIP, Output::BROWN));
                    $output->writeln('Use -f/--force option if you want to proceed anyway.');

                    throw new \InvalidArgumentException('IP address validation failed');
                }
            }
        }
        $this->maintenance->enable($allowedIPs);

        $output->writeln('Maintenance mode was enabled.');
    }
}
