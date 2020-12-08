<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Result;

class HtmlResponse extends \Awesome\Framework\Model\Result\Response
{
    /**
     * @inheritDoc
     */
    public function proceed(): void
    {
        $this->setHeader('Content-Type', 'text/html');

        parent::proceed();
    }
}
