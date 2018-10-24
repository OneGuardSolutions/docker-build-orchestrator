#!/usr/bin/env php
<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use OneGuard\DockerBuildOrchestrator\Application;

require __DIR__ . '/../vendor/autoload.php';

$application = new Application('@package_version@');
/** @noinspection PhpUnhandledExceptionInspection */
$application->run();
