<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $envRaw = $context['APP_ENV'] ?? 'dev';
    $env = is_string($envRaw) ? $envRaw : 'dev';
    if (!in_array($env, ['dev', 'test', 'prod'], true)) {
        $env = 'dev';
    }

    $debugRaw = $context['APP_DEBUG'] ?? ($env !== 'prod');
    $debug = filter_var($debugRaw, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
    if ($debug === null) {
        $debug = $env !== 'prod';
    }

    return new Kernel($env, $debug);
};
