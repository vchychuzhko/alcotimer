<?php
declare(strict_types=1);

namespace Vch\Framework\Model\Http\Response;

class Redirect extends \Vch\Framework\Model\Http\Response
{
    public const MOVED_PERMANENTLY_STATUS_CODE = 301;
    public const FOUND_STATUS_CODE = 302;
    public const TEMPORARY_REDIRECT_STATUS_CODE = 307;

    private string $redirectUrl;

    /**
     * Redirect constructor.
     * @param string $redirectUrl
     * @param int $status
     * @param array $headers
     */
    public function __construct(string $redirectUrl, int $status = self::FOUND_STATUS_CODE, array $headers = [])
    {
        parent::__construct('', $status, $headers);
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @inheritDoc
     */
    public function proceed()
    {
        $this->setHeader('Location', $this->redirectUrl);

        parent::proceed();
        exit();
    }

    /**
     * Set redirect URL.
     * @param string $redirectUrl
     * @return $this
     */
    public function setRedirectUrl(string $redirectUrl): self
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    /**
     * Get redirect URL.
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }
}
