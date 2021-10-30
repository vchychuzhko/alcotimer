<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Http;

/**
 * Class Request
 * @method string getAcceptType()
 * @method string|null getRoute()
 * @method string|null getEntity()
 * @method string|null getAction()
 * @method string getUserIp()
 * @method string getView()
 */
class Request extends \Awesome\Framework\Model\DataObject implements \Awesome\Framework\Model\SingletonInterface
{
    public const FORBIDDEN_REDIRECT_CODE = 403;
    public const NOTFOUND_REDIRECT_CODE = 404;

    public const HTTP_METHOD_GET = 'GET';
    public const HTTP_METHOD_POST = 'POST';

    public const SCHEME_HTTP  = 'http';
    public const SCHEME_HTTPS = 'https';

    public const DEFAULT_ROUTE = 'index';
    public const ROOT_ACTION_NAME = 'index_index_index';

    public const JSON_ACCEPT_HEADER = 'application/json';
    public const HTML_ACCEPT_HEADER = 'text/html';

    /**
     * @var string $url
     */
    private $url;

    /**
     * @var string $scheme
     */
    private $scheme;

    /**
     * @var string $host
     */
    private $host;

    /**
     * @var string $path
     */
    private $path;

    /**
     * @var string $method
     */
    private $method;

    /**
     * @var array $parameters
     */
    private $parameters;

    /**
     * @var array $cookies
     */
    private $cookies;

    /**
     * @var int|null $redirectStatusCode
     */
    private $redirectStatusCode;

    /**
     * @var string $fullActionName
     */
    private $fullActionName;

    /**
     * Request constructor.
     * @param string $url
     * @param string $method
     * @param array $parameters
     * @param array $cookies
     * @param int|null $redirectStatusCode
     * @param array $data
     */
    public function __construct(
        string $url,
        string $method = self::HTTP_METHOD_GET,
        array $parameters = [],
        array $cookies = [],
        ?int $redirectStatusCode = null,
        array $data = []
    ) {
        parent::__construct($data, true);
        $this->url = $url;
        $this->method = $method;
        $this->parameters = $parameters;
        $this->cookies = $cookies;
        $this->redirectStatusCode = $redirectStatusCode;
    }

    /**
     * Get request URL.
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get request scheme protocol.
     * @return string
     */
    private function getScheme(): string
    {
        if (!$this->scheme) {
            $this->scheme = parse_url($this->url, PHP_URL_SCHEME);
        }

        return $this->scheme;
    }

    /**
     * Check if request was performed via secure connection.
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->getScheme() === self::SCHEME_HTTPS;
    }

    /**
     * Get request URL host.
     * @return string
     */
    public function getHost(): string
    {
        if (!$this->host) {
            $this->host = parse_url($this->url, PHP_URL_HOST);
        }

        return $this->host;
    }

    /**
     * Get request URL path.
     * @return string
     */
    public function getPath(): string
    {
        if (!$this->path) {
            $this->path = rtrim(parse_url($this->url, PHP_URL_PATH), '/') ?: '/';
        }

        return $this->path;
    }

    /**
     * Get request full action name.
     * @return string
     */
    public function getFullActionName(): string
    {
        if (!$this->fullActionName) {
            $this->fullActionName = implode('_', [
                $this->getRoute() ?: self::DEFAULT_ROUTE,
                $this->getEntity() ?: 'index',
                $this->getAction() ?: 'index',
            ]);
        }

        return $this->fullActionName;
    }

    /**
     * Get request method.
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Check if request is POST.
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->getMethod() === self::HTTP_METHOD_POST;
    }

    /**
     * Get request parameter by key.
     * @param string $key
     * @return mixed
     */
    public function getParam(string $key)
    {
        return $this->parameters[$key] ?? null;
    }

    /**
     * Get request parameter by key transforming it to array.
     * Useful for parameters that can be passed as array, separated by commas.
     * @param string $key
     * @return array
     */
    public function getParamAsArray(string $key): array
    {
        $valueString = $this->getParam($key);

        return $valueString ? explode(',', $valueString) : [];
    }

    /**
     * Get all request parameters.
     * @return array
     */
    public function getParams(): array
    {
        return $this->parameters;
    }

    /**
     * Get request cookie by key.
     * @param string $key
     * @return string|null
     */
    public function getCookie(string $key): ?string
    {
        return $this->cookies[$key] ?? null;
    }

    /**
     * Get request redirect status code.
     * @return int|null
     */
    public function getRedirectStatusCode(): ?int
    {
        return $this->redirectStatusCode;
    }
}
