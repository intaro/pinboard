<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Usefull methods
 */
class SqlUtils
{
    public static function getDateSelectExpression(string $period, string $field = 'created_at'): string
    {
        if (false !== stripos($period, '1 day')) {
            return $field;
        }

        return "MIN($field)";
    }

    public static function getDateGroupExpression(string $period, string $field = 'created_at'): string
    {
        if (false !== stripos($period, '1 day')) {
            return $field;
        }
        if (false !== stripos($period, '1 week') || false !== stripos($period, '3 days')) {
            return "day($field), hour($field)";
        }

        return "day($field), round(hour($field) / 4)";
    }
}
