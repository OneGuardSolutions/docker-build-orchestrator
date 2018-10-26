<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OneGuard\DockerBuildOrchestrator\Builder\WorkingTree;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\NamedImage
 */
class NamedImageTest extends TestCase {
    /**
     * @covers ::__construct
     * @covers ::getDockerfilePath
     */
    public function testGetDockerfilePath() {
        $namedImage = new NamedImage('1', 'tests/_resources/docker/repositories-1/test/1/Dockerfile');

        $this->assertEquals(
            'tests/_resources/docker/repositories-1/test/1/Dockerfile',
            $namedImage->getDockerfilePath()
        );
    }
}
