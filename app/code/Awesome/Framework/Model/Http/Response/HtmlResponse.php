<?php

namespace Awesome\Framework\Model\Http\Response;

class HtmlResponse extends \Awesome\Framework\Model\Http\Response
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
