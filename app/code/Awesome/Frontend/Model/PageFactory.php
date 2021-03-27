<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Frontend\Model\Page;

class PageFactory extends \Awesome\Framework\Model\AbstractFactory
{
    /**
     * Create page object.
     * @param string $handle
     * @param string $view
     * @param array $handles
     * @return Page
     * @throws \Exception
     */
    public function create(string $handle, string $view, array $handles = []): Page
    {
        return $this->invoker->create(Page::class, [
            'data' => [
                'handle'  => $handle,
                'view'    => $view,
                'handles' => $handles ?: [$handle],
            ]
        ]);
    }
}
