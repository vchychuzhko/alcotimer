<?php

namespace Awesome\Framework\Model\Http;

class Response
{
    public const SUCCESS_STATUS_CODE = 200;
    public const FORBIDDEN_STATUS_CODE = 403;
    public const NOTFOUND_STATUS_CODE = 404;
    public const INTERNAL_ERROR_STATUS_CODE = 500;
    public const SERVICE_UNAVAILABLE_STATUS_CODE = 503;

    /**
     * @var string $content
     */
    protected $content;

    /**
     * @var int $status
     */
    protected $status;

    /**
     * @var array $headers
     */
    protected $headers;

    /**
     * Response constructor.
     * @param string $content
     * @param int $status
     * @param array $headers
     */
    public function __construct($content = '', $status = self::SUCCESS_STATUS_CODE, $headers = [])
    {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    /**
     * Prepare and return response.
     */
    public function proceed()
    {
        http_response_code($this->status);

        foreach ($this->headers as $key => $value) {
            header($key . ': ' . $value);
        }

        if ($this->content !== '') {
            echo $this->content;
        }
    }

    /**
     * Set response status code.
     * @param int $status
     * @return $this
     */
    public function setStatusCode($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get response status code.
     * @return int
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * Check if response is successful.
     * @return bool
     */
    public function isOk()
    {
        return $this->status === self::SUCCESS_STATUS_CODE;
    }

    /**
     * Set header to response.
     * Existing header with the same key will be overwritten.
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * Get response header by key.
     * @param string $key
     * @return string|null
     */
    public function getHeader($key)
    {
        return $this->headers[$key] ?? null;
    }

    /**
     * Get all response headers.
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Remove all headers from response.
     * @return $this
     */
    public function resetHeaders()
    {
        $this->headers = [];

        return $this;
    }

    /**
     * Set response content.
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get response content.
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
