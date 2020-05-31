<?php

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Response;

interface ActionInterface
{
    /**
     * Execute http action.
     * @param Request $request
     * @return Response
     */
    public function execute($request);
}
