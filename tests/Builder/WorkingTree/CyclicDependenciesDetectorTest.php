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

use OneGuard\DockerBuildOrchestrator\Builder\Builder;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\CyclicDependenciesDetector
 */
class CyclicDependenciesDetectorTest extends TestCase {
    /**
     * @var CyclicDependenciesDetector
     */
    private $detector;

    protected function setUp() {
        $this->detector = new CyclicDependenciesDetector();
        $this->workingTree = new WorkingTree();
    }

    /**
     * @covers ::detect
     * @covers ::getRelevantDependencies
     */
    public function testDetect() {
        $workingTree = (new Builder())->buildAll([
            __DIR__ . '/../../_resources/docker/repositories-1/',
            __DIR__ . '/../../_resources/docker/repositories-3/'
        ]);
        $result = $this->detector->detect($workingTree);

        $this->assertEquals([], $result);
    }

    /**
     * @covers ::detect
     */
    public function testDetectEmpty() {
        $workingTree = new WorkingTree();
        $result = $this->detector->detect($workingTree);

        $this->assertEquals([], $result);
    }

    /**
     * @covers ::detect
     * @covers ::getRelevantDependencies
     * @covers ::constructCycleFrom
     */
    public function testDetectWithCyclicDependency() {
        $workingTree = (new Builder())->buildAll([
            __DIR__ . '/../../_resources/docker/repositories-4/'
        ]);
        $result = $this->detector->detect($workingTree);

        $this->assertEquals(
            [
                ['test-4.docker.io/test/test:a', 'test-4.docker.io/test/test:b'],
                ['test-4.docker.io/test/test:a', 'test-4.docker.io/test/test:b', 'test-4.docker.io/test/test:c']
            ],
            $result
        );
    }
}
