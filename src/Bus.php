<?php

namespace EventBus;

/**
 * Class Bus.
 *
 * @package EventBus
 */
class Bus
{
    /**
     * Events that are to be run as part of the bus.
     *
     * @var array|\EventBus\Events\Event[]
     */
    protected $events;

    /**
     * Handler for any exceptions that are thrown within the event bus.
     *
     * @var null|string|array|\Closure
     */
    protected $exceptionHandler;

    /**
     * Handler for what should happen after the entire event bus has completed.
     *
     * @var array|\Closure[]|string[]
     */
    protected $thenHandlers = [];

    /**
     * Flag on whether or not to allow failures.
     *
     * @var bool
     */
    protected $allowFailures = false;

    /**
     * Bus constructor.
     *
     * @param array|string|\EventBus\Events\Event[] $events
     */
    function __construct($events)
    {
        if (!is_array($events)) {
            $events = (array)$events;
        }

        $this->events = $events;
    }

    /**
     * Create a new instance of the event bus statically.
     *
     * @param array|string|\EventBus\Events\Event[] $events
     *
     * @return \EventBus\Bus
     */
    public static function make($events): Bus
    {
        return new static($events);
    }

    /**
     * Event to run after the event bus has completed.
     *
     * @param string|array|\Closure $action
     *
     * @return \EventBus\Bus
     */
    public function then($action): Bus
    {
        $this->thenHandlers[] = $action;
        return $this;
    }

    /**
     * Method to handle any Exceptions that are thrown during the execution of
     * the event bus.
     *
     * @param string|array|\Closure $handler
     *
     * @return \EventBus\Bus
     */
    public function catch($handler): Bus
    {
        $this->exceptionHandler = $handler;
        return $this;
    }

    /**
     * Set the "allowFailures" flag to true. Setting this will mean that any
     * exceptions that are thrown will be passed to the catch method within the
     * Bus.
     *
     * @return $this
     */
    public function allowFailures(): Bus
    {
        $this->allowFailures = true;
        return $this;
    }

    /**
     * Execute the event bus.
     *
     * @return $this
     * @throws \Exception
     */
    public function dispatch(): Bus
    {
        foreach ($this->events as $event) {
            try {
                if (!method_exists($event, 'handle')) {
                    throw new \Exception(
                        sprintf("Handle method does not exist in event [%s]",
                            $event->eventClassName())
                    );
                }

                $event->handle();
            } catch (\Exception $exception) {
                $this->handleException($exception);
            }
        }

        foreach ($this->thenHandlers as $thenHandler) {
            call_user_func_array($thenHandler, [$this]);
        }

        return $this;
    }

    /**
     * Handle a given exception that has been thrown within the event bus.
     *
     * @param \Exception $exception
     *
     * @return mixed
     * @throws \Exception
     */
    private function handleException(\Exception $exception)
    {
        if (!$this->allowFailures) {
            return null;
        }

        if (empty($this->exceptionHandler)) {
            throw new \Exception("An exception was thrown within the event bus with the allow failures flag set to true, but no exception handler was passed");
        }

        return call_user_func($this->exceptionHandler, $exception);
    }
}
