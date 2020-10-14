<?php

namespace EventBus\Events;

/**
 * Class Event.
 *
 * @package EventBus\Events
 */
abstract class Event
{
    /**
     * Get the event class name.
     *
     * @return string
     */
    public function eventClassName(): string
    {
        return static::class;
    }

    /**
     * Get the Event name.
     *
     * @return string
     * @throws \Exception
     */
    public function getEventName(): string
    {
        throw new \Exception(
            sprintf("No event name for event [%s] has been set", $this->eventClassName())
        );
    }

    /**
     * Execute the event.
     *
     * @return mixed
     */
    abstract public function handle();
}
