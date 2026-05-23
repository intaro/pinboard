<?php

/**
 * @return array<string, string|int|float|bool|null>
 */
function pinba_get_info(): array
{
    $state = $GLOBALS['__pinba_test_state']['info'] ?? [];

    return is_array($state) ? $state : [];
}

/**
 * @param array<string, string|int|float|bool> $tags
 */
function pinba_timer_start(array $tags): mixed
{
    $state = $GLOBALS['__pinba_test_state'] ?? [];
    $timerId = ($state['timer_id'] ?? 0) + 1;

    $GLOBALS['__pinba_test_state']['timer_id'] = $timerId;
    $GLOBALS['__pinba_test_state']['starts'][] = [
        'timer_id' => $timerId,
        'tags' => $tags,
    ];

    return (object) ['timer_id' => $timerId, 'tags' => $tags];
}

/**
 * @param array<string, string|int|float|bool> $tags
 */
function pinba_timer_add(array $tags, int|float $time): void
{
    $GLOBALS['__pinba_test_state']['adds'][] = [
        'tags' => $tags,
        'time' => $time,
    ];
}

function pinba_timer_stop(mixed $timer = null): void
{
    $GLOBALS['__pinba_test_state']['stops'][] = $timer;
}

function pinba_test_reset(): void
{
    $GLOBALS['__pinba_test_state'] = [
        'info' => [],
        'timer_id' => 0,
        'starts' => [],
        'adds' => [],
        'stops' => [],
    ];
}

/**
 * @param array<string, string|int|float|bool|null> $info
 */
function pinba_test_set_info(array $info): void
{
    $GLOBALS['__pinba_test_state']['info'] = $info;
}

/**
 * @return array<string, mixed>
 */
function pinba_test_state(): array
{
    return $GLOBALS['__pinba_test_state'] ?? [];
}
