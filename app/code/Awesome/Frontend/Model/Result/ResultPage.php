<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\Result;

use Awesome\Frontend\Model\Page;

class ResultPage extends \Awesome\Framework\Model\Result\HtmlResponse
{
    /**
     * @var Page $page
     */
    private $page;

    /**
     * ResultPage constructor.
     * @param Page $page
     * @param int $status
     * @param array $headers
     */
    public function __construct(Page $page, int $status = self::SUCCESS_STATUS_CODE, array $headers = [])
    {
        parent::__construct('', $status, $headers);
        $this->page = $page;
    }

    /**
     * @inheritDoc
     */
    public function proceed(): void
    {
        if ($this->content === '') {
            $this->content = $this->page->render();
        }

        parent::proceed();
    }

    /**
     * @inheritDoc
     */
    public function getContent(): string
    {
        if ($this->content === '') {
            $this->content = $this->page->render();
        }

        return $this->content;
    }
}
