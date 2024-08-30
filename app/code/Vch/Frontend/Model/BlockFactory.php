<?php

namespace Vch\Frontend\Model;

use Vch\Frontend\Model\BlockInterface;

class BlockFactory extends \Vch\Framework\Model\AbstractFactory
{
    /**
     * Create block object.
     * @param string $blockId
     * @param array $params
     * @return BlockInterface
     */
    public function create(string $blockId, array $params = []): BlockInterface
    {
        return $this->invoker->create($blockId, $params);
    }
}
