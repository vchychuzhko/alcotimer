<?php

namespace Awesome\Framework\Model\Http;

/**
 * Class Request
 * @method string getFullActionName()
 * @method string getUserIp()
 * @method string getView()
 * @package Awesome\Framework\Model\Http
 */
class Request extends \Awesome\Framework\Model\DataObject
{
    public const FORBIDDEN_REDIRECT_CODE = 403;
    public const NOTFOUND_REDIRECT_CODE = 404;

    public const HTTP_METHOD_GET = 'GET';
    public const HTTP_METHOD_POST = 'POST';
    public const HTTP_METHOD_DELETE = 'DELETE';
    public const HTTP_METHOD_PUT = 'PUT';

    public const SCHEME_HTTP  = 'http';
    public const SCHEME_HTTPS = 'https';

    /**
     * @var string $url
     */
    private $url;

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
        return parse_url($this->url, PHP_URL_SCHEME);
    }

    /**
     * Get request URL host.
     * @return string
     */
    public function getHost()
    {
        return parse_url($this->url, PHP_URL_HOST);
    }

    /**
     * Get request URL path.
     * @return string
     */
    public function getPath()
    {
        return rtrim(parse_url($this->url, PHP_URL_PATH), '/') ?: '/';
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
     * Get request parameter.
     * Return all parameters if no key is specified.
     * @param string $key
     * @return string|array|null
     */
    public function getParam($key = '')
    {
        if ($key === '') {
            $param = $this->parameters;
        } else {
            $param = $this->parameters[$key] ?? null;
        }

        return $param;
    }

    /**
     * Get request cookie.
     * Return all cookies if no key is specified.
     * @param string $key
     * @return string|array|null
     */
    public function getCookie($key = '')
    {
        if ($key === '') {
            $cookie = $this->cookies;
        } else {
            $cookie = $this->cookies[$key] ?? null;
        }

        return $cookie;
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
