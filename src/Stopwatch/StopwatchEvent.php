<?php

declare(strict_types=1);

namespace App\Stopwatch;

class StopwatchEvent
{
    public function __construct(
        private readonly \PinbaTimerHandle|null $pinbaTimer = null
    ) {
    }

    public function stop(): void
    {
        if ($this->pinbaTimer !== null) {
            pinba_timer_stop($this->pinbaTimer);
        }
    }
}
