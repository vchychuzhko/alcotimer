<?php

namespace Awesome\Maintenance\Console;

use Awesome\Maintenance\Model\Maintenance;

class Enable extends \Awesome\Framework\Model\Cli\AbstractCommand
{
    /**
     * @var Maintenance $maintenance
     */
    private $maintenance;

    /**
     * Maintenance Enable constructor.
     */
    public function __construct()
    {
        $this->maintenance = new Maintenance();
    }

    /**
     * @inheritDoc
     */
    public static function getConfiguration()
    {
        return array_replace_recursive(parent::getConfiguration(), [
            'description' => 'Enable maintenance mode with list of allowed ids.',
            'options' => [
                'force' => [
                    'shortcut' => 'f',
                    'mode' => self::OPTION_OPTIONAL,
                    'description' => 'Force maintenance mode enabling',
                    'default' => null
                ]
            ],
            'arguments' => [
                'ip' => [
                    'position' => 0,
                    'mode' => self::ARGUMENT_OPTIONAL_ARRAY,
                    'description' => 'Ip addresses to exclude'
                ]
            ]
        ]);
    }

    /**
     * Enable maintenance mode.
     * @inheritDoc
     */
    public function execute($input, $output)
    {
        $allowedIPs = $input->getArgument();

        if ($this->validateIPs($allowedIPs) || $input->getOption('force')) {
            $this->maintenance->enable($allowedIPs);

            $output->writeln('Maintenance mode was enabled.');
        } else {
            $output->writeln('Provided IP addresses are not valid: Please, check them and try again.');
            $output->writeln('Use -f option if you are sure and want to proceed anyway.');
        }
    }

    /**
     * Validate provided IP addresses.
     * @param array $ips
     * @return bool
     */
    private function validateIPs($ips)
    {
        $valid = true;
        //@TODO: Move it to a separate validator

        foreach ($ips as $ip) {
            if (!$valid = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $invalid[] = $ip;
                break;
            }
        }

        return $valid;
    }
}
