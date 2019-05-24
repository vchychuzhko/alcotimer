<?php

namespace Ava\Console\Command;

class Cache extends \Ava\Console\AbstractCommand
{
    /**
     * @var array $methodMap
     */
    protected $methodMap = [
        'c' => 'clean',
        'cl' => 'clean',
        'cle' => 'clean',
        'clea' => 'clean',
        'clean' => 'clean'
    ];

    /**
     * @return string
     */
    public function clean()
    {
        return 'will be filled soon';
    }
}
