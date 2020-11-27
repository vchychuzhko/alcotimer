<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Result;

use Awesome\Framework\Model\Result\Response;

class ResponseFactory extends \Awesome\Framework\Model\AbstractFactory
{
    /**
     * Create response object.
     * @return Response
     */
    public function create(): Response
    {
        return $this->invoker->create(Response::class);
    }
}
