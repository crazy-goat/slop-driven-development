#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Kernel;
use CrazyGoat\WorkermanBundle\Runtime\Runner;

require_once dirname(__DIR__).'/config/bootstrap.php';

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'prod', (bool) ($_SERVER['APP_DEBUG'] ?? false));

$runner = new Runner($kernel);
$runner->run();
