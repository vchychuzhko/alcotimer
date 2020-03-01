<?php

namespace Awesome\Framework\Exception;

class NoSuchEntityException extends \LogicException
{
    /**
     * @var mixed $entity
     */
    private $entity;

    /**
     * NoSuchHandleException constructor.
     * @param string $message
     * @param mixed|null $entity
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = '', $entity = null, $code = 0 , \Exception $previous = null)
    {
        $this->entity = $entity;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get thrown object.
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
