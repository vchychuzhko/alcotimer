<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Result;

use Awesome\Framework\Model\Result\Redirect;

class RedirectFactory extends \Awesome\Framework\Model\AbstractFactory
{
    /**
     * Create response object.
     * @return Redirect
     */
    public function create(): Redirect
    {
        return $this->invoker->create(Redirect::class);
    }
}
