<?php

namespace Awesome\Frontend\Model;

use Awesome\Cache\Model\Cache;
use Awesome\Frontend\Model\Layout;

/**
 * Class Page
 * @method string getHandle()
 * @method string getView()
 * @method array getHandles()
 */
class Page extends \Awesome\Framework\Model\DataObject
{
    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var Layout $layout
     */
    private $layout;

    /**
     * Page constructor.
     * @param Cache $cache
     * @param Layout $layout
     * @param array $data
     */
    public function __construct(Cache $cache, Layout $layout, $data = [])
    {
        parent::__construct($data, true);
        $this->cache = $cache;
        $this->layout = $layout;
    }

    /**
     * Render the page.
     * @return string
     * @throws \Exception
     */
    public function render(): string
    {
        $content = '';
        $handle = $this->getHandle();
        $view = $this->getView();
        $handles = $this->getHandles() ?: [$handle];

        if ($handle && $view) {
            $content = $this->cache->get(
                Cache::FULL_PAGE_CACHE_KEY,
                $handle . '_' . $view,
                function () use ($handle, $view, $handles) {
                    $this->layout->init($handle, $view, $handles);

                    return $this->layout->render('root');
                }
            );
        }

        return $content;
    }
}
