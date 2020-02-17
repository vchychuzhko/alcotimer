<?php

namespace Awesome\Framework\Model;

interface AppInterface
{
    public const VERSION = '0.3.0';

    /**
     * Run the application.
     */
    public function run();
}
