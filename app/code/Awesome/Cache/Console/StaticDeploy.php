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
     */
    function __construct()
    {
        $this->staticContent = new \Awesome\Cache\Model\StaticContent();
    }

    /**
     * Regenerate static files.
     * @inheritDoc
     */
    public function execute($args = [])
    {
        $this->staticContent->deploy();
        //@TODO: implement view parameters

        return 'Static content was deployed.';
    }
}
