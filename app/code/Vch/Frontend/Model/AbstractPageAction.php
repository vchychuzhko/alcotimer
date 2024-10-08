<?php
declare(strict_types=1);

namespace Vch\Frontend\Model;

use Vch\Framework\Model\Http\Response\HtmlResponse;
use Vch\Framework\Model\Http\ResponseFactory;
use Vch\Framework\Model\ResponseInterface;
use Vch\Frontend\Model\Page\PageConfig;

abstract class AbstractPageAction extends \Vch\Framework\Model\AbstractAction
{
    protected string $pageLayout = 'default';

    private PageConfig $pageConfig;

    private PageFactory $pageFactory;

    /**
     * AbstractPageAction constructor.
     * @param PageConfig $pageConfig
     * @param PageFactory $pageFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        PageConfig $pageConfig,
        PageFactory $pageFactory,
        ResponseFactory $responseFactory
    ) {
        parent::__construct($responseFactory);
        $this->pageConfig = $pageConfig;
        $this->pageFactory = $pageFactory;
    }

    /**
     * Create and prepare page html response.
     * @param string|null $handle
     * @return HtmlResponse
     */
    protected function createPageResponse(string $handle = null): ResponseInterface
    {
        $page = $this->pageFactory->create($handle ?: $this->getPageLayout(), static::getView(), $this->getPageConfig());

        return $this->responseFactory->create(ResponseFactory::TYPE_HTML, [
            'content' => $page->render()
        ]);
    }

    /**
     * Get page layout.
     * @return string
     */
    protected function getPageLayout(): string
    {
        return $this->pageLayout;
    }

    /**
     * Get page configuration.
     * @return PageConfig
     */
    protected function getPageConfig(): PageConfig
    {
        return $this->pageConfig;
    }
}
