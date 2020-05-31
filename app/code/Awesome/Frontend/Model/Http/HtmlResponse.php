<?php

namespace Awesome\Frontend\Model\Http;

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
