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
     * @inheritDoc
     */
    public function __construct($options = [], $arguments = [])
    {
        $this->staticContent = new \Awesome\Cache\Model\StaticContent();
        parent::__construct($options, $arguments);
    }

    /**
     * Regenerate static files.
     * @inheritDoc
     */
    public function execute($output)
    {
        $this->staticContent->deploy();
        //@TODO: implement view parameters

        $output->writeln('Static content was deployed.');
    }
}
