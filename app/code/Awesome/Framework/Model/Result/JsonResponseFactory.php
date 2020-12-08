<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Result;

use Awesome\Framework\Model\Result\JsonResponse;

class JsonResponseFactory extends \Awesome\Framework\Model\AbstractFactory
{
    /**
     * Create JSON response object.
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        return $this->invoker->create(JsonResponse::class);
    }
}
