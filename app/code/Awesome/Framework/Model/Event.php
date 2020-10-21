<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

class Event extends \Awesome\Framework\Model\DataObject
{
    /**
     * @var string $eventName
     */
    private $eventName;

    /**
     * Event constructor.
     * @param string $eventName
     * @param array $data
     */
    public function __construct(string $eventName, array $data = [])
    {
        parent::__construct($data);
        $this->eventName = $eventName;
    }

    /**
     * Get event name.
     * @return string
     */
    public function getEventName(): string
    {
        return $this->eventName;
    }
}
