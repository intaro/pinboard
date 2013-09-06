<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['params'] = Symfony\Component\Yaml\Yaml::parse(__DIR__.'/../config/parameters.yml');

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_mysql',
        'dbname'   => $app['params']['db']['name'],
        'host'     => $app['params']['db']['host'],
        'user'     => $app['params']['db']['user'],
        'password' => $app['params']['db']['pass'],
    )
));
if ($app['params']['cache'] == 'array')
    $app['db']->getConfiguration()->setResultCacheImpl(new \Doctrine\Common\Cache\ArrayCache());
else
    $app['db']->getConfiguration()->setResultCacheImpl(new \Doctrine\Common\Cache\ApcCache());

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