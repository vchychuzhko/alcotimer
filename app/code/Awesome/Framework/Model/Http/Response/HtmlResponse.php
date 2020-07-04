<?php

namespace Awesome\Framework\Model\Http\Response;

class HtmlResponse extends \Awesome\Framework\Model\Http\Response
{
    /**
     * @inheritDoc
     */
    public function proceed()
    {
        $this->addHeader('Content-Type', 'text/html');

        parent::proceed();
    }
}
