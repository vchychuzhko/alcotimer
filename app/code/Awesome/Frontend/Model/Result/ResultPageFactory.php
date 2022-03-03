<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\Result;

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
     * ResultPageFactory constructor.
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
    public function create(string $handle): HtmlResponse
    {
        $page = $this->pageFactory->create($handle);

        return $this->invoker->create(HtmlResponse::class, [
            'content' => $page->render(),
        ]);
    }
}
