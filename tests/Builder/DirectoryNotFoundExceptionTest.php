<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OneGuard\DockerBuildOrchestrator\Builder;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \OneGuard\DockerBuildOrchestrator\Builder\DirectoryNotFoundException
 */
class DirectoryNotFoundExceptionTest extends TestCase {
    /**
     * @covers ::__construct
     * @covers ::getPath
     */
    public function testGetPath() {
        $exception = new DirectoryNotFoundException('/test');

        $this->assertEquals('/test', $exception->getPath());
    }
}
