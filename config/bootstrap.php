<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/.env')) {
    $dotenv = new Dotenv();

    // Force override — system ma APP_ENV=prod, a my chcemy dev z .env
    $dotenv->usePutenv(true);

    // Ładujemy .env, potem .env.local, nadpisując wszystko
    $dotenv->loadEnv(dirname(__DIR__).'/.env', overrideExistingVars: true);
}
