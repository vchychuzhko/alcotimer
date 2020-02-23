<?php

namespace Awesome\Cache\Console;

class StaticDeploy extends \Awesome\Framework\Model\Cli\AbstractCommand
{
    /**
     * @var \Awesome\Cache\Model\StaticContent $staticContent
     */
    private $staticContent;

    /**
     * StaticDeploy constructor.
     */
    public function __construct()
    {
        $this->staticContent = new \Awesome\Cache\Model\StaticContent();
    }

    /**
     * Generate static files.
     * @inheritDoc
     */
    public function execute($input, $output)
    {
        $this->staticContent->deploy();
        //@TODO: Add view attribute

        $output->writeln('Static content was deployed.');
    }
}
