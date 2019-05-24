<?php

namespace Ava\Console;

class AbstractCommand
{
    /**
     * @param $methodName string
     * @return string
     */
    public function getMethodName($methodName) {
        return $this->methodMap[$methodName] ?? '';
    }
}
