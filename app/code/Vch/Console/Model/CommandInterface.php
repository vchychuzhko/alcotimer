<?php
declare(strict_types=1);

namespace Vch\Console\Model;

use Vch\Console\Model\Cli\Input;
use Vch\Console\Model\Cli\Input\InputDefinition;
use Vch\Console\Model\Cli\Output;

interface CommandInterface
{
    /**
     * Define all data related to console command.
     * @return InputDefinition
     */
    public static function configure(): InputDefinition;

    /**
     * Run the console command.
     * @param Input $input
     * @param Output $output
     */
    public function execute(Input $input, Output $output);

    /**
     * Display help for the command.
     * @param Input $input
     * @param Output $output
     */
    public function help(Input $input, Output $output);
}
