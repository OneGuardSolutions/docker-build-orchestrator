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
 * @coversDefaultClass \OneGuard\DockerBuildOrchestrator\Builder\BrokenAliasesDetector
 */
class BrokenAliasesDetectorTest extends TestCase {
    /**
     * @covers ::detect
     */
    public function testDetect() {
        $detector = new BrokenAliasesDetector();
        $workingTree = (new Builder())->buildAll([__DIR__ . '/../_resources/docker/repositories-2/']);
        $result = $detector->detect($workingTree);

        $this->assertCount(1, $result);
        $this->assertEquals('test-2.docker.io/test/test:2', $result[0]->getFullName());
        $this->assertEquals('2.0', $result[0]->getReference());
    }
}
