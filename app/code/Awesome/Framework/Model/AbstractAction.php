<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Http\Context;
use Awesome\Framework\Model\Result\HtmlResponseFactory;
use Awesome\Framework\Model\Result\JsonResponseFactory;
use Awesome\Framework\Model\Result\RedirectFactory;
use Awesome\Framework\Model\Result\ResponseFactory;

abstract class AbstractAction extends \Awesome\Framework\Model\DataObject implements \Awesome\Framework\Model\ActionInterface
{
    /**
     * @var JsonResponseFactory $responseFactory
     */
    protected $jsonResponseFactory;

    /**
     * @var HtmlResponseFactory $responseFactory
     */
    protected $htmlResponseFactory;

    /**
     * @var RedirectFactory $responseFactory
     */
    protected $redirectFactory;

    /**
     * @var ResponseFactory $responseFactory
     */
    protected $responseFactory;

    /**
     * AbstractAction constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($data, true);
        $this->jsonResponseFactory = $context->getJsonResponseFactory();
        $this->htmlResponseFactory = $context->getHtmlResponseFactory();
        $this->redirectFactory = $context->getRedirectFactory();
        $this->responseFactory = $context->getResponseFactory();
    }
}
