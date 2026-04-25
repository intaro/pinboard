<?php

declare(strict_types=1);

namespace App\Stopwatch;

class StopwatchEvent
{
    public function __construct(
        private readonly mixed $pinbaTimer = null
    ) {
    }

    public function stop(): void
    {
        if ($this->pinbaTimer) {
            pinba_timer_stop($this->pinbaTimer);
        }
    }
}
