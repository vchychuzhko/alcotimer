<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\Http;

use Awesome\Framework\Model\Invoker;
use Awesome\Framework\Model\Http\Response\HtmlResponse;
use Awesome\Frontend\Model\PageFactory;

class PageResponseFactory extends \Awesome\Framework\Model\Http\ResponseFactory
{
    private PageFactory $pageFactory;

    /**
     * PageResponseFactory constructor.
     * @param Invoker $invoker
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Invoker $invoker,
        PageFactory $pageFactory
    ) {
        parent::__construct($invoker);
        $this->pageFactory = $pageFactory;
    }

    /**
     * Create html page response object.
     * @param string $handle
     * @return HtmlResponse
     */
    public function createPage(string $handle): HtmlResponse
    {
        $page = $this->pageFactory->create($handle);

        return $this->create(self::TYPE_HTML, [
            'content' => $page->render(),
        ]);
    }
}
