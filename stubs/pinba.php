<?php

/**
 * @return array<string, string|int|float|bool|null>
 */
function pinba_get_info(): array
{
    return [];
}

/**
 * @param array<string, string|int|float|bool> $tags
 */
function pinba_timer_start(array $tags): mixed
{
    return null;
}

/**
 * @param array<string, string|int|float|bool> $tags
 */
function pinba_timer_add(array $tags, int|float $time): void
{
}

function pinba_timer_stop(mixed $timer = null): void
{
}
