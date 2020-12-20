<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Result\ResponseFactory;

abstract class AbstractAction extends \Awesome\Framework\Model\DataObject implements \Awesome\Framework\Model\ActionInterface
{
    /**
     * @var ResponseFactory $responseFactory
     */
    protected $responseFactory;

    /**
     * AbstractAction constructor.
     * @param ResponseFactory $responseFactory
     * @param array $data
     */
    public function __construct(ResponseFactory $responseFactory, array $data = [])
    {
        parent::__construct($data, true);
        $this->responseFactory = $responseFactory;
    }
}
