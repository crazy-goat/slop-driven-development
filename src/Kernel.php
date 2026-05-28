<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getCacheDir(): string
    {
        if ($cacheDir = ($_ENV['APP_CACHE_DIR'] ?? $_SERVER['APP_CACHE_DIR'] ?? null)) {
            return $cacheDir . '/' . $this->environment;
        }

        return parent::getCacheDir();
    }

    public function getLogDir(): string
    {
        if ($logDir = ($_ENV['APP_LOG_DIR'] ?? $_SERVER['APP_LOG_DIR'] ?? null)) {
            return $logDir;
        }

        return parent::getLogDir();
    }
}
