<?php

namespace Ava\Console\Command;

class Cache extends \Ava\Console\AbstractCommand
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
