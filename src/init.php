<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['params'] = Symfony\Component\Yaml\Yaml::parse(__DIR__.'/../config/parameters.yml');

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {

    // add
    if (!isset($app['params']['base_url']) || empty($app['params']['base_url'])) {
        $app['params']['base_url'] = '/';
    } elseif (stubstr($app['params']['base_url'], 0, -1) != '/') {
        $app['params']['base_url'] .= '/';
    }
    $twig->addGlobal('base_url', $app['params']['base_url']);

    return $twig;
}));

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());


$dbOptions = array(
    'driver'   => 'pdo_mysql',
    'dbname'   => $app['params']['db']['name'],
    'host'     => $app['params']['db']['host'],
    'user'     => $app['params']['db']['user'],
    'password' => $app['params']['db']['pass'],
);
if (isset($app['params']['db']['port'])) {
    $dbOptions['port'] = $app['params']['db']['port'];
}

$app->register(new Silex\Provider\DoctrineServiceProvider(), array('db.options' => $dbOptions));

//query caching
$cacheClassName =
    'Doctrine\\Common\\Cache\\' .
    (isset($app['params']['cache']) ?
        Doctrine\Common\Util\Inflector::classify($app['params']['cache']) :
        'Array'
    ) .
    'Cache'
    ;

$app['db']->getConfiguration()->setResultCacheImpl(new $cacheClassName());

$users = array();
if (isset($app['params']['secure']['users'])) {
    foreach ($app['params']['secure']['users'] as $userName => $userData) {
        $users[$userName] = array(
                "ROLE_USER",
                $userData['password'],
            );
    }
}

if (isset($app['params']['secure']['enable']) && $app['params']['secure']['enable']) {
    $app->register(new Silex\Provider\SecurityServiceProvider(), array(
        'security.firewalls' => array(
            'secure_area' => array(
                'pattern' => "^/",
                'http' => true,
                'users' => $users,
            ),
        )
    ));
}