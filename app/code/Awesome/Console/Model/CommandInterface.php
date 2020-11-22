<?php
declare(strict_types=1);

namespace Awesome\Console\Model;

use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;

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
     * @return void
     */
    public function execute(Input $input, Output $output): void;

    /**
     * Display help for the command.
     * @param Input $input
     * @param Output $output
     * @return void
     */
    public function help(Input $input, Output $output): void;
}
