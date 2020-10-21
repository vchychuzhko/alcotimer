<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Response;

abstract class AbstractAction extends \Awesome\Framework\Model\DataObject
{
    /**
     * AbstractAction constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data, true);
    }

    /**
     * Execute http action.
     * @param Request $request
     * @return Response
     */
    abstract public function execute(Request $request): Response;
}
