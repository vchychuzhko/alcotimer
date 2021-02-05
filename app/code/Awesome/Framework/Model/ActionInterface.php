<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\ResponseInterface;

interface ActionInterface
{
    /**
     * Execute http action.
     * @param Request $request
     * @return ResponseInterface
     */
    public function execute(Request $request): ResponseInterface;
}
