<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\Action;

use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Response;
use Awesome\Framework\Model\Http\Response\HtmlResponse;
use Awesome\Frontend\Model\PageFactory;

/**
 * Class LayoutHandler
 * @method string getHandle()
 * @method array getHandles()
 * @method int getStatus()
 */
class LayoutHandler extends \Awesome\Framework\Model\AbstractAction
{
    public const HOMEPAGE_HANDLE_CONFIG = 'web/homepage';

    public const FORBIDDEN_PAGE_HANDLE = 'forbidden_index_index';
    public const NOTFOUND_PAGE_HANDLE = 'notfound_index_index';

    /**
     * @var PageFactory $pageFactory
     */
    private $pageFactory;

    /**
     * LayoutHandler constructor.
     * @param PageFactory $pageFactory
     * @param array $data
     */
    public function __construct(
        PageFactory $pageFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->pageFactory = $pageFactory;
    }

    /**
     * Render html page according to request path and view.
     * @inheritDoc
     * @throws \Exception
     */
    public function execute(Request $request): Response
    {
        $handle = $this->getHandle();
        $handles = $this->getHandles();
        $view = $request->getView();

        $page = $this->pageFactory->create($handle, $view, $handles); // @TODO: add resultPageFactory

        return new HtmlResponse($page->render(), $this->getStatus());
    }
}
