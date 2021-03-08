<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Frontend\Model\GeneratorInterface;

class GeneratorFactory extends \Awesome\Framework\Model\AbstractFactory
{
    /**
     * Create static generator object.
     * @param string $type
     * @return GeneratorInterface
     */
    public function create(string $type): GeneratorInterface
    {
        return $this->invoker->create($type);
    }
}
