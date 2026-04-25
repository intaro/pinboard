<?php

declare(strict_types=1);

namespace App\Tests\Unit\Stopwatch;

use App\Stopwatch\Stopwatch;
use PHPUnit\Framework\TestCase;

final class StopwatchTest extends TestCase
{
    protected function setUp(): void
    {
        pinba_test_reset();
        pinba_test_set_info([
            'hostname' => 'test-host',
            'server_name' => 'test-server',
        ]);
    }

    public function testStartAddsPinbaMetadataAndDerivesCategoryFromGroup(): void
    {
        $stopwatch = new Stopwatch();
        $event = $stopwatch->start([
            'group' => 'main::overview',
            'custom' => 'value',
        ]);

        $state = pinba_test_state();
        self::assertCount(1, $state['starts']);

        $tags = $state['starts'][0]['tags'];
        self::assertSame('test-host', $tags['__hostname']);
        self::assertSame('test-server', $tags['__server_name']);
        self::assertSame('main::overview', $tags['group']);
        self::assertSame('main', $tags['category']);
        self::assertSame('value', $tags['custom']);

        $event->stop();

        $state = pinba_test_state();
        self::assertCount(1, $state['stops']);
        self::assertSame($state['starts'][0]['timer_id'], $state['stops'][0]->timer_id);
    }

    public function testAddUsesMergedPinbaMetadata(): void
    {
        $stopwatch = new Stopwatch();
        $stopwatch->add([
            'group' => 'timer::details',
        ], 1.25);

        $state = pinba_test_state();
        self::assertCount(1, $state['adds']);
        self::assertSame(1.25, $state['adds'][0]['time']);
        self::assertSame('timer::details', $state['adds'][0]['tags']['group']);
        self::assertSame('test-host', $state['adds'][0]['tags']['__hostname']);
        self::assertSame('test-server', $state['adds'][0]['tags']['__server_name']);
    }
}
