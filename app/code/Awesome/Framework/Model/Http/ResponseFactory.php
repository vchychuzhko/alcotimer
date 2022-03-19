<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Http;

use Awesome\Framework\Model\ResponseInterface;
use Awesome\Framework\Model\Http\Response\HtmlResponse;
use Awesome\Framework\Model\Http\Response\JsonResponse;
use Awesome\Framework\Model\Http\Response\Redirect;
use Awesome\Framework\Model\Http\Response;

class ResponseFactory extends \Awesome\Framework\Model\AbstractFactory
{
    public const TYPE_JSON     = 'json';
    public const TYPE_HTML     = 'html';
    public const TYPE_RAW      = 'raw';
    public const TYPE_REDIRECT = 'redirect';

    /**
     * @var array $typeMap
     */
    private array $typeMap = [
        self::TYPE_JSON     => JsonResponse::class,
        self::TYPE_HTML     => HtmlResponse::class,
        self::TYPE_RAW      => Response::class,
        self::TYPE_REDIRECT => Redirect::class,
    ];

    /**
     * Create response object according to provided type.
     * @param string $type
     * @param array $params
     * @return ResponseInterface
     */
    public function create(string $type = self::TYPE_RAW, array $params = []): ResponseInterface
    {
        if (!isset($this->typeMap[$type])) {
            throw new \InvalidArgumentException(__('Response type "%1" is not registered', $type));
        }

        return $this->invoker->create($this->typeMap[$type], $params);
    }
}
