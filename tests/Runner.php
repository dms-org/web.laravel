<?php

namespace Dms;

use Dms\Common\Testing;

$projectAutoLoaderPath    = __DIR__ . '/../vendor/autoload.php';
$dependencyAutoLoaderPath = __DIR__ . '/../../../../autoload.php';

if (file_exists($projectAutoLoaderPath)) {
    $composerAutoLoader = require $projectAutoLoaderPath;
} elseif (file_exists($dependencyAutoLoaderPath)) {
    $composerAutoLoader = require $dependencyAutoLoaderPath;
} else {
    throw new \Exception('Cannot load tests for ' . __NAMESPACE__ . ' under ' . __DIR__ . ': please load via composer');
}

$composerAutoLoader->addPsr4(__NAMESPACE__ . '\\', __DIR__);

Testing\Bootstrapper::run(__NAMESPACE__, __DIR__, 'phpunit.xml');