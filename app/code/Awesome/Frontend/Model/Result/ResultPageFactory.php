<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\Result;

use Awesome\Framework\Model\Invoker;
use Awesome\Frontend\Model\PageFactory;
use Awesome\Frontend\Model\Result\ResultPage;

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
    public function __construct(Invoker $invoker, PageFactory $pageFactory)
    {
        parent::__construct($invoker);
        $this->pageFactory = $pageFactory;
    }

    /**
     * Create page response object.
     * @param string $handle
     * @param string $view
     * @param array $handles
     * @return ResultPage
     */
    public function create(string $handle, string $view, array $handles = []): ResultPage
    {
        $handles = $handles ?: [$handle];
        $page = $this->pageFactory->create($handle, $view, $handles);

        return $this->invoker->create(ResultPage::class, [
            'page' => $page,
        ]);
    }
}
