<?php

namespace Awesome\Cache\Console;

class StaticDeploy extends \Awesome\Console\Model\AbstractCommand
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
    public function execute()
    {
        $this->staticContent->deploy();
        //@TODO: implement view parameters

        return 'Static content was deployed.';
    }
}
