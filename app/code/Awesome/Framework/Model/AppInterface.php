<?php

namespace Awesome\Framework\Model;

interface AppInterface
{
    public const VERSION = '0.3.1';

    /**
     * Run the application.
     */
    public function run();
}
