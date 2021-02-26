<?php
declare(strict_types=1);

namespace Awesome\Customer\Model;

class VisitorLogger extends \Awesome\Framework\Model\AbstractLogger
{
    private const VISITOR_LOG_FILE = 'visitor.log';

    /**
     * Log visited pages.
     * @param string $visitorInfo
     * @return $this
     */
    public function logVisitor(string $visitorInfo): self
    {
        $this->write(self::VISITOR_LOG_FILE, $visitorInfo);

        return $this;
    }
}
