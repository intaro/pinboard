<?php

declare(strict_types=1);

namespace App\Tests\Unit\Utils;

use App\Utils\SqlUtils;
use PHPUnit\Framework\TestCase;

final class SqlUtilsTest extends TestCase
{
    public function testDateSelectExpressionForOneDayKeepsField(): void
    {
        self::assertSame('created_at', SqlUtils::getDateSelectExpression('1 day'));
        self::assertSame('timestamp', SqlUtils::getDateSelectExpression('1 day', 'timestamp'));
    }

    public function testDateSelectExpressionForLongerPeriodsUsesMin(): void
    {
        self::assertSame('MIN(created_at)', SqlUtils::getDateSelectExpression('3 days'));
        self::assertSame('MIN(timestamp)', SqlUtils::getDateSelectExpression('1 month', 'timestamp'));
    }

    public function testDateGroupExpressionForOneDayKeepsField(): void
    {
        self::assertSame('created_at', SqlUtils::getDateGroupExpression('1 day'));
        self::assertSame('timestamp', SqlUtils::getDateGroupExpression('1 day', 'timestamp'));
    }

    public function testDateGroupExpressionForThreeDaysAndWeekUsesHourBuckets(): void
    {
        self::assertSame('day(created_at), hour(created_at)', SqlUtils::getDateGroupExpression('3 days'));
        self::assertSame('day(timestamp), hour(timestamp)', SqlUtils::getDateGroupExpression('1 week', 'timestamp'));
    }

    public function testDateGroupExpressionForMonthUsesQuarterDayBuckets(): void
    {
        self::assertSame('day(created_at), round(hour(created_at) / 4)', SqlUtils::getDateGroupExpression('1 month'));
    }
}
