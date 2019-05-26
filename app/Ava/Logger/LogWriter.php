<?php

namespace Ava\Logger;

class LogWriter
{
    private const EXCEPTION_LOG_FILE = 'var/log/exception.log';

    /**
     * Write all Errors, Warnings and Exceptions to log file
     * @param string $string
     */
    public function write($string)
    {
        $foo = $string;
    }
}
