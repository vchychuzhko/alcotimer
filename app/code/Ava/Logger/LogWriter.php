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
        $content = (string) @file_get_contents(BP . DS . self::EXCEPTION_LOG_FILE);
        file_put_contents(
            BP . DS . self::EXCEPTION_LOG_FILE,
            ($content ? "$content\n" : '') . date('m/d/Y h:i:s a', time()) . ' - ' . $string
        );
    }
}
