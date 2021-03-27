<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\Result;

use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Invoker;
use Awesome\Framework\Model\Result\HtmlResponse;
use Awesome\Frontend\Model\PageFactory;

class ResultPageFactory extends \Awesome\Framework\Model\AbstractFactory
{
    /**
     * @var PageFactory $pageFactory
     */
    private $pageFactory;

    /**
     * @var Request $request
     */
    private $request;

    /**
     * ResultPageFactory constructor.
     * @param Invoker $invoker
     * @param PageFactory $pageFactory
     */
    public function __construct(Invoker $invoker, PageFactory $pageFactory)
    {
        parent::__construct($invoker);
        $this->pageFactory = $pageFactory;
    }

    /**
     * Set result page request.
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Create html page response object.
     * @param string|null $handle
     * @param string|null $view
     * @param array $handles
     * @return HtmlResponse
     */
    public function create(?string $handle = null, ?string $view = null, array $handles = []): HtmlResponse
    {
        if (!$handle) {
            if ($this->request === null) {
                throw new \RuntimeException('Request object was not provided so result page handle cannot be retrieved');
            }
            $handle = $this->request->getFullActionName();
        }
        if (!$view) {
            if ($this->request === null) {
                throw new \RuntimeException('Request object was not provided so result page view cannot be retrieved');
            }
            $view = $this->request->getView();
        }
        $handles = $handles ?: [$handle];
        $page = $this->pageFactory->create($handle, $view, $handles);

        return $this->invoker->create(HtmlResponse::class, [
            'content' => $page->render(),
        ]);
    }
}
