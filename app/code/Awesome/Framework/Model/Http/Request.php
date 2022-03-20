<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Http;

use Awesome\Framework\Model\Http;

class Request extends \Awesome\Framework\Model\Singleton
{
    public const METHOD_POST = 'POST';

    public const SCHEME_HTTP  = 'http';
    public const SCHEME_HTTPS = 'https';

    public const ACCEPT_HEADER_JSON = 'application/json';
    public const ACCEPT_HEADER_HTML = 'text/html';

    private string $url = '';

    private string $scheme = '';

    private string $path = '/';

    private string $method = '';

    private array $parameters = [];

    private array $cookies = [];

    private ?string $acceptType = null;

    private string $userIp = '';

    /**
     * Request constructor.
     * Initialize fields from global variables.
     */
    protected function __construct()
    {
        $this->scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? self::SCHEME_HTTPS : self::SCHEME_HTTP;
        $this->url = $this->scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $this->path = rtrim(parse_url($this->url, PHP_URL_PATH), '/') ?: '/';

        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->parameters = array_merge($_GET, $_POST);
        $this->cookies = $_COOKIE;

        $this->acceptType = isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] !== '*/*'
            ? strtok($_SERVER['HTTP_ACCEPT'], ',')
            : null;
        $this->userIp = $_SERVER['REMOTE_ADDR'];
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
     * Check if request was sent via secure connection.
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->scheme === self::SCHEME_HTTPS;
    }

    /**
     * Get request URL path.
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
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
        return $this->getMethod() === self::METHOD_POST;
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
        $param = $this->getParam($key);

        return $param ? explode(',', $param) : [];
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
     * Get request accept type if specified.
     * @return string|null
     */
    public function getAcceptType(): ?string
    {
        return $this->acceptType;
    }

    /**
     * Get user IP address.
     * @return string
     */
    public function getUserIp(): string
    {
        return $this->userIp;
    }
}
