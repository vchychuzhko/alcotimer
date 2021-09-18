<?php
declare(strict_types=1);

namespace Awesome\Customer\Model;

class VisitorLogger extends \Awesome\Framework\Model\AbstractLogger
{
    private const VISITOR_LOG_FILE = 'visitor.log';

    /**
     * Log visit information.
     * @param string $visitInfo
     * @return $this
     */
    public function logVisit(string $visitInfo): self
    {
        return $this->write(self::VISITOR_LOG_FILE, $visitInfo);
    }
}
