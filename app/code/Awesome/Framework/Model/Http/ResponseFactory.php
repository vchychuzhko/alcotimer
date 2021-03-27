<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Http;

use Awesome\Framework\Model\ResponseInterface;
use Awesome\Framework\Model\Result\HtmlResponse;
use Awesome\Framework\Model\Result\JsonResponse;
use Awesome\Framework\Model\Result\Redirect;
use Awesome\Framework\Model\Result\Response;

class ResponseFactory extends \Awesome\Framework\Model\AbstractFactory
{
    public const TYPE_JSON     = 'json';
    public const TYPE_HTML     = 'html';
    public const TYPE_RAW      = 'raw';
    public const TYPE_REDIRECT = 'redirect';

    /**
     * @var array $typeMap
     */
    private $typeMap = [
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
            throw new \LogicException(sprintf('Response type "%s" is not registered', $type));
        }

        return $this->invoker->create($this->typeMap[$type], $params);
    }
}
