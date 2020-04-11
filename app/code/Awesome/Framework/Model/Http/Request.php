<?php

namespace Awesome\Framework\Model\Http;

class Request
{
    public const HTTP_STATUS_OK = 200;

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
     * @var int|null $redirectStatus
     */
    private $redirectStatus;

    /**
     * @var string|null $userIPAddress
     */
    private $userIPAddress;

    /**
     * Request constructor.
     * @param string $url
     * @param string $method
     * @param array $parameters
     * @param array $cookies
     * @param int|null $redirectStatus
     * @param string|null $userIPAddress
     */
    public function __construct(
        $url,
        $method = self::HTTP_METHOD_GET,
        $parameters = [],
        $cookies = [],
        $redirectStatus = null,
        $userIPAddress = null
    ) {
        $this->url = $url;
        $this->method = $method;
        $this->parameters = $parameters;
        $this->cookies = $cookies;
        $this->redirectStatus = $redirectStatus;
        $this->userIPAddress = $userIPAddress;
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
     * Get URL path.
     * @return string
     */
    private function getScheme()
    {
        return parse_url($this->url, PHP_URL_SCHEME);
    }

    /**
     * Check if request was performed via secure connection.
     * @return bool
     */
    public function isSecure()
    {
        $scheme = $this->getScheme();

        return $scheme === self::SCHEME_HTTPS;
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
            $param = $this->parameters[$key] ?? null;
        } else {
            $param = $this->parameters;
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
            $cookie = $this->cookies[$key] ?? null;
        } else {
            $cookie = $this->cookies;
        }

        return $cookie;
    }

    /**
     * Get request redirect status code.
     * @return int|null
     */
    public function getRedirectStatusCode()
    {
        return $this->redirectStatus;
    }

    /**
     * Get user IP address.
     * @return string|null
     */
    public function getUserIPAddress()
    {
        return $this->userIPAddress;
    }
}
