<?php
declare(strict_types=1);

namespace Vch\Framework\Model;

use Vch\Framework\Model\Http\Request;
use Vch\Framework\Model\ResponseInterface;

interface ActionInterface
{
    /**
     * Execute http action.
     * @param Request $request
     * @return ResponseInterface
     */
    public function execute(Request $request): ResponseInterface;

    /**
     * Get action view according to its controller folder.
     * @return string
     */
    public static function getView(): string;
}
