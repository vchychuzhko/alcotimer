<?php

namespace Ava\Console;

class AbstractCommand
{
    /**
     * Parse input arguments.
     * @param array $args
     * @return array
     */
    protected function parseArguments($args) {
        $arguments = [];

        foreach ($args as $arg) {
            list($argument, $value) = explode('=', str_replace('--', '', $arg));
            $arguments[$argument][] = $value;
        }

        return $arguments;
    }
}
