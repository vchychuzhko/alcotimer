<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Frontend\Model\Page;

class PageFactory extends \Awesome\Framework\Model\AbstractFactory
{
    /**
     * Create page object.
     * @param string $handle
     * @return Page
     * @throws \Exception
     */
    public function create(string $handle): Page
    {
        return $this->invoker->create(Page::class, ['data' => ['handle'  => $handle]]);
    }
}
