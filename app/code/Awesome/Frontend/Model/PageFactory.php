<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Frontend\Model\Page;
use Awesome\Frontend\Model\Page\PageConfig;

class PageFactory extends \Awesome\Framework\Model\AbstractFactory
{
    /**
     * Create page object.
     * @param string $handle
     * @param string $view
     * @param PageConfig $pageConfig
     * @return Page
     */
    public function create(string $handle, string $view, PageConfig $pageConfig): Page
    {
        return $this->invoker->create(Page::class, [
            'data' => [
                'handle'      => $handle,
                'view'        => $view,
                'page_config' => $pageConfig
            ]
        ]);
    }
}
