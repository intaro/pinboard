<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$parameters = Symfony\Component\Yaml\Yaml::parse(__DIR__.'/../config/parameters.yml');

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_mysql',
        'dbname'   => $parameters['db_name'],
        'host'     => $parameters['db_host'],
        'user'     => $parameters['db_user'],
        'password' => $parameters['db_pass'],
    )
));
