<?php

namespace Awesome\Framework\Model\Http;

/**
 * Class Request
 * @method string getAcceptType()
 * @method string getFullActionName()
 * @method string getUserIp()
 * @method string getView()
 */
class Request extends \Awesome\Framework\Model\DataObject implements \Awesome\Framework\Model\SingletonInterface
{
    public const FORBIDDEN_REDIRECT_CODE = 403;
    public const NOTFOUND_REDIRECT_CODE = 404;

    public const HTTP_METHOD_GET = 'GET';
    public const HTTP_METHOD_POST = 'POST';
    public const HTTP_METHOD_DELETE = 'DELETE';
    public const HTTP_METHOD_PUT = 'PUT';

    public const SCHEME_HTTP  = 'http';
    public const SCHEME_HTTPS = 'https';

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
     * Request constructor.
     * @param string $url
     * @param string $method
     * @param array $parameters
     * @param array $cookies
     * @param int|null $redirectStatusCode
     * @param array $data
     */
    public function __construct(
        $url,
        $method = self::HTTP_METHOD_GET,
        $parameters = [],
        $cookies = [],
        $redirectStatusCode = null,
        $data = []
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
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get URL scheme protocol.
     * @return string
     */
    private function getScheme()
    {
        if (!$this->scheme) {
            $this->scheme = parse_url($this->url, PHP_URL_SCHEME);
        }

        return $this->scheme;
    }

    /**
     * Get request URL host.
     * @return string
     */
    public function getHost()
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
    public function getPath()
    {
        if (!$this->path) {
            $this->path = rtrim(parse_url($this->url, PHP_URL_PATH), '/') ?: '/';
        }

        return $this->path;
    }

    /**
     * Check if request was performed via secure connection.
     * @return bool
     */
    public function isSecure()
    {
        return $this->getScheme() === self::SCHEME_HTTPS;
    }

    /**
     * Get request method.
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get request parameter by key.
     * @param string $key
     * @return string|null
     */
    public function getParam($key)
    {
        return $this->parameters[$key] ?? null;
    }

    /**
     * Get all request parameters.
     * @return array
     */
    public function getParams()
    {
        return $this->parameters;
    }

    /**
     * Get request cookie by key.
     * @param string $key
     * @return string|null
     */
    public function getCookie($key)
    {
        return $this->cookies[$key] ?? null;
    }

    /**
     * Get all request cookies.
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Get request redirect status code.
     * @return int|null
     */
    public function getRedirectStatusCode()
    {
        return $this->redirectStatusCode;
    }
}
