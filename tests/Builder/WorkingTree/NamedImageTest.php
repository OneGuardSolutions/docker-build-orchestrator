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
        $namedImage = new NamedImage('1', __DIR__ . '/../../_resources/docker/repositories-1/test/1.2.3/Dockerfile');

        $this->assertEquals(
            'tests/_resources/docker/repositories-1/test/1.2.3/Dockerfile',
            $this->relativePaths(realpath($namedImage->getDockerfilePath()))
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getDependencies
     */
    public function testGetDependencies() {
        $namedImage = new NamedImage('1', __DIR__ . '/../../_resources/docker/repositories-1/test/1.2.3-dev/Dockerfile');

        $this->assertEquals(['busybox:latest', 'test.docker.io/test/test:1.2.3'], $namedImage->getDependencies());
    }

    /**
     * @covers ::__construct
     * @covers ::getDependencies
     */
    public function testGetDependenciesFileNotExists() {
        $namedImage = new NamedImage('1', '/not/exist');

        $this->assertEmpty($namedImage->getDependencies());
    }

    private function relativePaths(string $content) {
        return preg_replace('/^.*tests\/_resources\/docker\//', 'tests/_resources/docker/', $content);
    }
}
