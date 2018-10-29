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
 * @coversDefaultClass \OneGuard\DockerBuildOrchestrator\Builder\BuildOrderProcessor
 */
class BuildOrderProcessorTest extends TestCase {
    /**
     * @covers ::determineOrder
     * @covers ::addDependencies
     * @covers ::getTags
     * @covers ::getRelevantDependencies
     */
    public function testDetermineOrder() {
        $workingTree = (new Builder())->buildAll([
            __DIR__ . '/../_resources/docker/repositories-1',
            __DIR__ . '/../_resources/docker/repositories-3'
        ]);
        $processor = new BuildOrderProcessor();

        $this->assertEquals(
            [
                'test.docker.io/test/test:1.2.3',
                'test.docker.io/test/test:1.2.3-dev',
                'test.docker.io/test/test-2:1',
                'test-3.docker.io/test/test:1'
            ],
            $processor->determineOrder($workingTree)
        );
    }
}
