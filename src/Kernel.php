<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * Unknown APP_ENV values (e.g. a typo such as "production") fail fast
     * instead of booting with an unintended configuration.
     *
     * @return list<string>
     */
    protected function getAllowedEnvs(): array
    {
        return ['prod', 'dev', 'test'];
    }

    public function getCacheDir(): string
    {
        $cacheDir = $_SERVER['APP_CACHE_DIR'] ?? $_ENV['APP_CACHE_DIR'] ?? null;
        if (is_string($cacheDir) && $cacheDir !== '') {
            return rtrim($cacheDir, '/') . '/' . $this->environment;
        }

        return dirname(__DIR__) . '/var/cache/' . $this->environment;
    }
}
