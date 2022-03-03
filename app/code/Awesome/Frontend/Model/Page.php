<?php

namespace Awesome\Frontend\Model;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Locale;
use Awesome\Frontend\Model\Layout;

/**
 * Class Page
 * @method string getHandle()
 * @method string getView()
 * @method array getHandles()
 * @method string|null getLocale()
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
     * @var Locale $locale
     */
    private $locale;

    /**
     * Page constructor.
     * @param Cache $cache
     * @param Layout $layout
     * @param Locale $locale
     * @param array $data
     */
    public function __construct(Cache $cache, Layout $layout, Locale $locale, $data = [])
    {
        parent::__construct($data, true);
        $this->cache = $cache;
        $this->layout = $layout;
        $this->locale = $locale;
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
        $view = Http::FRONTEND_VIEW; // @TODO: $this->getView();
        $locale = $this->getLocale() ?: $this->locale->getLocale();

        if ($handle && $view) {
            $content = $this->cache->get(
                Cache::FULL_PAGE_CACHE_KEY,
                $handle . '//' . $view . '//' . $locale,
                function () use ($handle, $view) {
                    $this->layout->init($handle, $view);

                    return $this->layout->render('root');
                }
            );
        }

        return $content;
    }
}
