<?php

declare(strict_types=1);

namespace App\Tests\Unit\Utils;

use App\Utils\DateTimeUtils;
use PHPUnit\Framework\TestCase;

final class DateTimeUtilsTest extends TestCase
{
    private string $originalTimezone = 'UTC';

    protected function setUp(): void
    {
        $this->originalTimezone = date_default_timezone_get();
        date_default_timezone_set('Europe/Moscow');
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->originalTimezone);
    }

    public function testFormatsStorageDateTimeInServerTimezone(): void
    {
        self::assertSame(
            '2026-07-20 15:34:56',
            DateTimeUtils::formatStorageDateTimeForServer('2026-07-20 12:34:56')
        );
    }

    public function testFormatsChartDateInServerTimezone(): void
    {
        self::assertSame(
            '2026,6,20,15,34',
            DateTimeUtils::chartDateFromStorageDateTime('2026-07-20 12:34:56')
        );
    }

    public function testFormatsChartLabelInServerTimezone(): void
    {
        self::assertSame(
            '2026-07-20 15:34',
            DateTimeUtils::chartLabelFromStorageDateTime('2026-07-20 12:34:56')
        );
    }

    public function testFormatsUnixTimestampInServerTimezone(): void
    {
        self::assertSame('03:00:00', DateTimeUtils::formatUnixTimestampForServer(0));
    }

    public function testConfiguresServerTimezone(): void
    {
        DateTimeUtils::configureServerTimezone('Asia/Yekaterinburg');

        self::assertSame('Asia/Yekaterinburg', date_default_timezone_get());
        self::assertSame('05:00:00', DateTimeUtils::formatUnixTimestampForServer(0));
    }

    public function testIgnoresInvalidServerTimezone(): void
    {
        DateTimeUtils::configureServerTimezone('invalid/timezone');

        self::assertSame('Europe/Moscow', date_default_timezone_get());
    }

    public function testInvalidStorageDateTimeFallsBackToOriginalValue(): void
    {
        self::assertSame('not-a-date', DateTimeUtils::formatStorageDateTimeForServer('not-a-date'));
    }
}
