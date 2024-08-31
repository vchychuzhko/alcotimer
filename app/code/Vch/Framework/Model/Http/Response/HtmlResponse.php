<?php
declare(strict_types=1);

namespace Vch\Framework\Model\Http\Response;

class HtmlResponse extends \Vch\Framework\Model\Http\Response
{
    /**
     * @inheritDoc
     */
    public function proceed()
    {
        $this->setHeader('Content-Type', 'text/html');

        parent::proceed();
    }
}
