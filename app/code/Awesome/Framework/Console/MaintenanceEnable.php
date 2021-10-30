<?php
declare(strict_types=1);

namespace Awesome\Framework\Console;

use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Framework\Model\Maintenance;
use Awesome\Framework\Model\Validator\IpValidator;

class MaintenanceEnable extends \Awesome\Console\Model\AbstractCommand
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
            ->setDescription('Enable maintenance mode')
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
        $allowedIps = $input->getArgument('ips') ?: [];

        if ($allowedIps && !$input->getOption('force')) {
            foreach ($allowedIps as $allowedIp) {
                if (!$this->ipValidator->valid($allowedIp)) {
                    $output->writeln(
                        'Provided IP address is not valid, please, check it and try again: ' . $output->colourText($allowedIp, Output::BROWN)
                    );
                    $output->writeln('Use -f/--force option if you want to proceed anyway.');

                    throw new \InvalidArgumentException('IP address validation failed');
                }
            }
        }
        $this->maintenance->enable($allowedIps);

        $output->writeln('Maintenance mode has been enabled.');

        if ($allowedIps) {
            $output->writeln('Except for: ' . $output->colourText(implode(' ', $allowedIps), Output::BROWN));
        }
    }
}
