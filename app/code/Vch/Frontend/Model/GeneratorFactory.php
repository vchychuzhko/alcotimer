<?php
declare(strict_types=1);

namespace Vch\Frontend\Model;

use Vch\Frontend\Model\GeneratorInterface;

class GeneratorFactory extends \Vch\Framework\Model\AbstractFactory
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
