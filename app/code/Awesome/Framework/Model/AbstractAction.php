<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

abstract class AbstractAction extends \Awesome\Framework\Model\DataObject implements \Awesome\Framework\Model\ActionInterface
{
    /**
     * AbstractAction constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data, true);
    }
}
