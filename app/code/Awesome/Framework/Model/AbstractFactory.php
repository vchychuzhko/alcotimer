<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Invoker;

abstract class AbstractFactory
{
    protected Invoker $invoker;

    /**
     * AbstractFactory constructor.
     * @param Invoker $invoker
     */
    public function __construct(
        Invoker $invoker
    ) {
        $this->invoker = $invoker;
    }
}
