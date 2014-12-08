<?php

namespace Pinboard\Stopwatch;

class StopwatchEvent
{
    protected $pinbaTimer;

    public function __construct($pinbaTimer = null)
    {
        $this->pinbaTimer = $pinbaTimer;
    }

    public function stop()
    {
        if ($this->pinbaTimer) {
            pinba_timer_stop($this->pinbaTimer);
        }
    }
}
