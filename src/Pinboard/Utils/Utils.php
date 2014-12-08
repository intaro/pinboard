<?php

namespace Pinboard\Utils;

/**
* Usefull methods
*/
class Utils
{
    public static function generateColor()
    {
        return dechex(rand(0,10000000));
    }

    /**
     * Return string or array with hosts regexp for access
     *
     * @access public
     * @param mixed $app
     * @return string|array
     */
    public function getUserAccessHostsRegexp($app)
    {
        $hostsRegExp = ".*";

        if (isset($app['params']['secure']['enable']) && $app['params']['secure']['enable']) {
            $user = $app['security']->getToken()->getUser();
            $hostsRegExp = isset($app['params']['secure']['users'][$user->getUsername()]['hosts'])
                        ? $app['params']['secure']['users'][$user->getUsername()]['hosts']
                        : ".*";

            $hostsRegExp = is_array($hostsRegExp) ? $hostsRegExp : array($hostsRegExp);
            //ignore empty rules
            foreach ($hostsRegExp as &$rgx) {
                if (trim($rgx) == ".*") {
                    unset($rgx);
                }
            }

            if (!sizeof($hostsRegExp)) {
                return '.*';
            }
        }

        return $hostsRegExp;
    }

    public static function checkUserAccess($app, $serverName)
    {
        $hostsRegExp = self::getUserAccessHostsRegexp($app);

        $hasAccess = false;
        $hostsRegExp = is_array($hostsRegExp) ? $hostsRegExp : array($hostsRegExp);
        foreach ($hostsRegExp as $regexp) {
            if (preg_match("/" . $regexp . "/", $serverName)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            $app->abort(403, "Access denied");
        }

        return $hasAccess;
    }

    public static function parseRequestTags($request, $tagsFilter = null)
    {
        //request tags matches the tags' filter
        if (sizeof($tagsFilter)) {
            if (!$request['tags_cnt']) {
                return false;
            }

            foreach ($tagsFilter as $tagName => $tagValue) {
                if (false === stripos($request['tags'], $tagName.'='.$tagValue)) {
                    return false;
                }
            }
        }

        if ($request['tags_cnt']) {
            $r = explode(',', $request['tags']);
            $request['tags'] = array();
            foreach ($r as $k) {
                $k = explode('=', $k);
                if (sizeof($k) > 1) {
                    $request['tags'][trim($k[0])] = trim($k[1]);
                }
                elseif (sizeof($k) == 1) {
                    $request['tags'][trim($k[0])] = null;
                }
            }
        }

        return $request;
    }

    public static function urlDecode($s)
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
        )*$%xs', $decodeString))
            return $decodeString;

        return $s;
    }
}
