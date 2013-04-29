<?php
require __DIR__.'/../src/init.php';

//$app['debug'] = true;

include __DIR__.'/../src/Pinboard/Controller/_before.php';
$app->mount('/',       include __DIR__.'/../src/Pinboard/Controller/index.php');
$app->mount('/server', include __DIR__.'/../src/Pinboard/Controller/server.php');

$app->run();