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
                if (trim($hostsRegExp) == "") {
                    $hosts = ".*";
                }
            }
        }

        if (!preg_match("/" . $hostsRegExp . "/", $serverName)) {
            $app->abort(403, "Access denied");
        }
    }

    public function parseRequestTags($request)
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
}
