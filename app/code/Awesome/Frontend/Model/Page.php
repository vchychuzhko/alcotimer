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
        $handle = $this->getHandle();
        $view = $this->getView();

        return $this->cache->get(Cache::FULL_PAGE_CACHE_KEY, $handle . '_' . $view, function () use ($handle, $view) {
            $this->layout->init($handle, $view, $this->getHandles());

            return $this->layout->render('root');
        });
    }
}
