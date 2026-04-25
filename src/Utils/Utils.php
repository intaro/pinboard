<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Usefull methods
 */
class Utils
{
    public static function generateColor(): string
    {
        return str_pad(dechex(random_int(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    public static function parseRequestTags(array $request, array $tagsFilter = []): array|bool
    {
        //request tags matches the tags' filter
        if (count($tagsFilter)) {
            if (!$request['tags_cnt']) {
                return false;
            }

            foreach ($tagsFilter as $tagName => $tagValue) {
                if (stripos($request['tags'], "$tagName=$tagValue") === false) {
                    return false;
                }
            }
        }

        if ($request['tags_cnt']) {
            $r = explode(',', $request['tags']);
            $request['tags'] = [];

            foreach ($r as $k) {
                $k = explode('=', $k);

                if (count($k) > 1) {
                    $request['tags'][trim($k[0])] = trim($k[1]);
                } elseif (count($k) === 1) {
                    $request['tags'][trim($k[0])] = null;
                }
            }
        }

        return $request;
    }

    public static function urlDecode(string $s): string
    {
        $decodeString = urldecode($s);

        if (preg_match('%^(?:
              [\x09\x0A\x0D\x20-\x7E]            # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
            | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
        )*$%xs', $decodeString)) {
            return $decodeString;
        }

        return $s;
    }
}
