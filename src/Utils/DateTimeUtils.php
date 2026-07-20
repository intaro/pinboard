<?php

declare(strict_types=1);

namespace App\Utils;

use DateTimeImmutable;
use DateTimeZone;
use Exception;

final class DateTimeUtils
{
    public const string STORAGE_TIMEZONE = 'UTC';
    public const string DEFAULT_DATETIME_FORMAT = 'Y-m-d H:i:s';

    public static function formatStorageDateTimeForServer(string $value, string $format = self::DEFAULT_DATETIME_FORMAT): string
    {
        $dateTime = self::parseStorageDateTime($value);
        if ($dateTime === null) {
            return $value;
        }

        return $dateTime->setTimezone(self::serverTimezone())->format($format);
    }

    public static function chartDateFromStorageDateTime(string $value): string
    {
        $dateTime = self::parseStorageDateTime($value);
        if ($dateTime === null) {
            return '1970,0,01,00,00';
        }

        $localDateTime = $dateTime->setTimezone(self::serverTimezone());

        return sprintf(
            '%s,%d,%s',
            $localDateTime->format('Y'),
            ((int) $localDateTime->format('n')) - 1,
            $localDateTime->format('d,H,i')
        );
    }

    public static function storageDateTimeAgo(string $period, string $format = self::DEFAULT_DATETIME_FORMAT): string
    {
        try {
            $dateTime = (new DateTimeImmutable('now', self::storageTimezone()))->modify('-' . $period);
        } catch (Exception) {
            return (new DateTimeImmutable('now', self::storageTimezone()))->format($format);
        }

        return $dateTime->format($format);
    }

    public static function formatUnixTimestampForServer(int $timestamp, string $format = 'H:i:s'): string
    {
        return (new DateTimeImmutable('@' . $timestamp))
            ->setTimezone(self::serverTimezone())
            ->format($format);
    }

    private static function parseStorageDateTime(string $value): ?DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($value, self::storageTimezone());
        } catch (Exception) {
            return null;
        }
    }

    private static function storageTimezone(): DateTimeZone
    {
        return new DateTimeZone(self::STORAGE_TIMEZONE);
    }

    private static function serverTimezone(): DateTimeZone
    {
        return new DateTimeZone(date_default_timezone_get());
    }
}
