<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Result;

use Awesome\Framework\Model\Result\HtmlResponse;

class HtmlResponseFactory extends \Awesome\Framework\Model\AbstractFactory
{
    /**
     * Create HTML response object.
     * @return HtmlResponse
     */
    public function create(): HtmlResponse
    {
        return $this->invoker->create(HtmlResponse::class);
    }
}
