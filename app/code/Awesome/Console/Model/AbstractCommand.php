<?php

namespace Awesome\Console\Model;

use Awesome\Console\Model\Console\Output;

abstract class AbstractCommand
{
    /**
     * @var array $options
     */
    protected $options;

    /**
     * @var array $arguments
     */
    protected $arguments;

    /**
     * AbstractCommand constructor.
     * @param array $options
     * @param array $arguments
     */
    public function __construct($options = [], $arguments = [])
    {
        $this->options = $options;
        $this->arguments = $arguments;
    }

    /**
     * Run the console command.
     * @param Output $output
     * @return string
     */
    abstract public function execute($output);

    /**
     * Determine if help should be shown.
     * @return bool
     */
    protected function showHelp()
    {
        return isset($this->options['h']) || isset($this->options['help']);
    }
}
