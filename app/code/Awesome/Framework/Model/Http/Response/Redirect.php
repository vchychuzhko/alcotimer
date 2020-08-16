<?php

namespace Awesome\Framework\Model\Http\Response;

class Redirect extends \Awesome\Framework\Model\Http\Response
{
    public const MOVED_PERMANENTLY_STATUS_CODE = 301;
    public const FOUND_STATUS_CODE = 302;
    public const TEMPORARY_REDIRECT_STATUS_CODE = 307;

    /**
     * @var string $redirectUrl
     */
    private $redirectUrl;

    /**
     * Redirect constructor.
     * @param string $redirectUrl
     * @inheritDoc
     */
    public function __construct($redirectUrl, $status = self::FOUND_STATUS_CODE, $headers = [])
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
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    /**
     * Get redirect URL.
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }
}
