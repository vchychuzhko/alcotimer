<?php
declare(strict_types=1);

namespace Vch\Framework\Model\Http;

class Response implements \Vch\Framework\Model\ResponseInterface
{
    protected string $content;

    protected int $status;

    protected array $headers;

    /**
     * Response constructor.
     * @param string $content
     * @param int $status
     * @param array $headers
     */
    public function __construct(string $content = '', int $status = self::SUCCESS_STATUS_CODE, array $headers = [])
    {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    /**
     * @inheritDoc
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
    public function setStatusCode(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get response status code.
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * Check if response is successful.
     * @return bool
     */
    public function isOk(): bool
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
    public function setHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * Set headers to response.
     * Existing headers will be overwritten.
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Get response header by key.
     * @param string $key
     * @return string|null
     */
    public function getHeader(string $key): ?string
    {
        return $this->headers[$key] ?? null;
    }

    /**
     * Get all response headers.
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Remove all headers from response.
     * @return $this
     */
    public function resetHeaders(): self
    {
        $this->headers = [];

        return $this;
    }

    /**
     * Set response content.
     * @param string $content
     * @return $this
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get response content.
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
