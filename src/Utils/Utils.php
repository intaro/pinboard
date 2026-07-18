<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\User;
use App\Security\FileUser;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Usefull methods
 */
class Utils
{
    /**
     * Normalizes a `hosts` config value to a single regexp string, or null if unrestricted.
     *
     * The legacy (Silex-era) config allowed both a string and a list of regexps per user;
     * empty and '.*' entries mean "no restriction" and are dropped. Multiple patterns are
     * combined into one alternation that works both in PCRE and MySQL REGEXP.
     */
    public static function normalizeHostsConfig(mixed $hosts): ?string
    {
        $patterns = [];
        foreach (is_array($hosts) ? $hosts : [$hosts] as $item) {
            if (!is_string($item)) {
                continue;
            }
            $item = trim($item);
            if ($item === '' || $item === '.*') {
                continue;
            }
            $patterns[] = $item;
        }

        if ($patterns === []) {
            return null;
        }

        return count($patterns) === 1 ? $patterns[0] : '(' . implode(')|(', $patterns) . ')';
    }

    /**
     * Returns the hosts regexp for the current user (FileUser or DB User), or '.*' if unrestricted.
     */
    public static function getUserHostsRegexp(?UserInterface $user): string
    {
        $hosts = null;

        if ($user instanceof FileUser) {
            $hosts = $user->getHosts();
        } elseif ($user instanceof User) {
            $hosts = $user->getHosts();
        }

        if ($hosts !== null && trim($hosts) !== '' && trim($hosts) !== '.*') {
            return $hosts;
        }

        return '.*';
    }

    /**
     * Returns true if the user is allowed to see the given server name.
     */
    public static function userCanAccessServer(?UserInterface $user, string $serverName): bool
    {
        $regexp = self::getUserHostsRegexp($user);
        if ($regexp === '.*') {
            return true;
        }

        return (bool) preg_match('/' . $regexp . '/', $serverName);
    }

    public static function generateColor(): string
    {
        return str_pad(dechex(random_int(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    /**
     * @param array<string, mixed>  $request
     * @param array<string, string> $tagsFilter
     * @return array<string, mixed>|false
     */
    public static function parseRequestTags(array $request, array $tagsFilter = []): array|false
    {
        //request tags matches the tags' filter
        if (count($tagsFilter)) {
            if (!$request['tags_cnt']) {
                return false;
            }

            $tagsRaw = $request['tags'];
            if (!is_string($tagsRaw)) {
                return false;
            }
            foreach ($tagsFilter as $tagName => $tagValue) {
                if (stripos($tagsRaw, "$tagName=$tagValue") === false) {
                    return false;
                }
            }
        }

        if ($request['tags_cnt']) {
            $tagsStr = $request['tags'];
            if (!is_string($tagsStr)) {
                return $request;
            }
            $r = explode(',', $tagsStr);
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
