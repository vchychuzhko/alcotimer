<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Http\ResponseFactory;

abstract class AbstractAction implements \Awesome\Framework\Model\ActionInterface
{
    /**
     * @var ResponseFactory $responseFactory
     */
    protected $responseFactory;

    /**
     * AbstractAction constructor.
     * @param ResponseFactory $responseFactory
     */
    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }
}
