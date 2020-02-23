<?php

namespace Awesome\Framework\Model\Cli;

use Awesome\Framework\Model\Cli\Input;
use Awesome\Framework\Model\Cli\Output;

abstract class AbstractCommand
{
    public const OPTION_REQUIRED = 'required';
    public const OPTION_OPTIONAL = 'optional';

    public const ARGUMENT_OPTIONAL_ARRAY = 'optional_array';

    /**
     * Define all data related to console command.
     * @return array
     */
    public static function getConfiguration()
    {
        return [
            'description' => '',
            'options' => [
                'help' => [
                    'shortcut' => 'h',
                    'mode' => self::OPTION_OPTIONAL,
                    'description' => 'Display this help message',
                    'default' => null
                ],
                'quiet' => [
                    'shortcut' => 'q',
                    'mode' => self::OPTION_OPTIONAL,
                    'description' => 'Do not output any message',
                    'default' => null
                ],
                'version' => [
                    'shortcut' => 'v',
                    'mode' => self::OPTION_OPTIONAL,
                    'description' => 'Display this application version',
                    'default' => null
                ],
                'no-interaction' => [
                    'shortcut' => 'n',
                    'mode' => self::OPTION_OPTIONAL,
                    'description' => 'Do not ask any interactive questions',
                    'default' => null
                ]
            ],
            'arguments' => []
        ];
    }

    /**
     * Run the console command.
     * @param Input $input
     * @param Output $output
     * @return string
     */
    abstract public function execute($input, $output);
}
