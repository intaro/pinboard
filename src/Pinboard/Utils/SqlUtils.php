<?php

namespace Pinboard\Utils;

/**
* Usefull methods
*/
class SqlUtils
{
    public static function getDateGroupExpression($period, $field = 'created_at')
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
