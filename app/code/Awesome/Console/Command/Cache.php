<?php

namespace Awesome\Console\Command;

class Cache //extends \Awesome\Console\Model\AbstractCommand
{
    public const DEPLOYED_VERSION_FILE = 'pub' . DS . 'static' . DS . 'deployed_version.txt';

    /**
     * Clean static files.
     * @return string
     */
    public function clean()
    {
        return 'Cache was cleaned.';
    }
}
