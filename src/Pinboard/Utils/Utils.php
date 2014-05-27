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

    public static function checkUserAccess($app, $serverName) {
        $hostsRegExp = ".*";
        if (isset($app['params']['secure']['enable'])) {
            if ($app['params']['secure']['enable'] == "true") {
                $user = $app['security']->getToken()->getUser();
                $hostsRegExp = isset($app['params']['secure']['users'][$user->getUsername()]['hosts'])
                            ? $app['params']['secure']['users'][$user->getUsername()]['hosts']
                            : ".*";
                if (!is_array($hostsRegExp)) {
                    $hostsRegExp = array($hostsRegExp);
                }
                foreach ($hostsRegExp as &$rgx) {
                    if (trim($rgx) == "") {
                        $rgx = ".*";
                    }
                }
            }
        }

        $hasAccess = false;
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

    public static function parseRequestTags($request)
    {
        if ($request['tags_cnt']) {
            $r = explode(',', $request['tags']);
            $request['tags'] = array();
            foreach ($r as $k) {
                $k = explode('=', $k);
                if (sizeof($k) > 1) {
                    $request['tags'][$k[0]] = $k[1];
                }
                elseif (sizeof($k) == 1) {
                    $request['tags'][$k[0]] = null;
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
