<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Http;

use Awesome\Framework\Model\Result\HtmlResponseFactory;
use Awesome\Framework\Model\Result\JsonResponseFactory;
use Awesome\Framework\Model\Result\RedirectFactory;
use Awesome\Framework\Model\Result\ResponseFactory;

class Context
{
    /**
     * @var JsonResponseFactory $jsonResponseFactory
     */
    private $jsonResponseFactory;

    /**
     * @var HtmlResponseFactory $htmlResponseFactory
     */
    private $htmlResponseFactory;

    /**
     * @var RedirectFactory $redirectFactory
     */
    private $redirectFactory;

    /**
     * @var ResponseFactory $responseFactory
     */
    private $responseFactory;

    /**
     * Context constructor.
     * @param JsonResponseFactory $jsonResponseFactory
     * @param HtmlResponseFactory $htmlResponseFactory
     * @param RedirectFactory $redirectFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        HtmlResponseFactory $htmlResponseFactory,
        RedirectFactory $redirectFactory,
        ResponseFactory $responseFactory
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->htmlResponseFactory = $htmlResponseFactory;
        $this->redirectFactory = $redirectFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Get JSON response factory.
     * @return JsonResponseFactory
     */
    public function getJsonResponseFactory(): JsonResponseFactory
    {
        return $this->jsonResponseFactory;
    }

    /**
     * Get HTML response factory.
     * @return HtmlResponseFactory
     */
    public function getHtmlResponseFactory(): HtmlResponseFactory
    {
        return $this->htmlResponseFactory;
    }

    /**
     * Get redirect response factory.
     * @return RedirectFactory
     */
    public function getRedirectFactory(): RedirectFactory
    {
        return $this->redirectFactory;
    }

    /**
     * Get response factory.
     * @return ResponseFactory
     */
    public function getResponseFactory(): ResponseFactory
    {
        return $this->responseFactory;
    }
}
