<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Http\ResponseFactory;
use Awesome\Framework\Model\Http\Router;

abstract class AbstractAction implements \Awesome\Framework\Model\ActionInterface
{
    protected ResponseFactory $responseFactory;

    /**
     * AbstractAction constructor.
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ResponseFactory $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @inheritDoc
     */
    public static function getView(): string
    {
        return preg_match('/\\\\' . Router::ADMINHTML_CONTROLLER_FOLDER . '\\\\/', static::class)
            ? Http::BACKEND_VIEW
            : Http::FRONTEND_VIEW;
    }
}
