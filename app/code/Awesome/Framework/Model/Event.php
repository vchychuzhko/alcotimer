<?php

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
    public function __construct($eventName, $data = [])
    {
        parent::__construct($data);
        $this->eventName = $eventName;
    }

    /**
     * Get event name.
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }
}
