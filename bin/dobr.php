<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Command\BuildCommand;
use Symfony\Component\Console\Application;

$application = new Application('dobr', '@package_version@');
$application->add(new BuildCommand());
/** @noinspection PhpUnhandledExceptionInspection */
$application->run();