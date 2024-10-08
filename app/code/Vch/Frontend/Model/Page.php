<?php

namespace Vch\Frontend\Model;

use Vch\Cache\Model\Cache;
use Vch\Framework\Model\Http;
use Vch\Framework\Model\Locale;
use Vch\Frontend\Model\Layout;

/**
 * Class Page
 * @method string getHandle()
 * @method string getView()
 * @method string|null getLocale()
 */
class Page extends \Vch\Framework\Model\DataObject
{
    private Cache $cache;

    private Layout $layout;

    private Locale $locale;

    /**
     * Page constructor.
     * @param Cache $cache
     * @param Layout $layout
     * @param Locale $locale
     * @param array $data
     */
    public function __construct(
        Cache $cache,
        Layout $layout,
        Locale $locale,
        array $data = []
    ) {
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
        $view = $this->getView();
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
