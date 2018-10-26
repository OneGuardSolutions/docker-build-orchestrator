<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Visitor;

use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Alias;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Repository;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\WorkingTree;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Visitor\SimpleVisitor
 */
class SimpleVisitorTest extends TestCase {
    /**
     * @covers ::visit
     */
    public function testVisit() {
        $workingTree = new WorkingTree();
        $repository1 = new Repository('test-1');
        $repository2 = new Repository('test-2');
        $workingTree->addRepository($repository2);
        $workingTree->addRepository($repository1);

        /* @var SimpleVisitor|MockObject $mockVisitor */
        $mockVisitor = $this->getMockBuilder(SimpleVisitor::class)
            ->setMethods(['beforeWorkingTree', 'visitRepository', 'afterWorkingTree'])
            ->getMockForAbstractClass();

        $mockVisitor->expects($this->at(0))
            ->method('beforeWorkingTree')
            ->with($this->equalTo($workingTree));
        $mockVisitor->expects($this->exactly(2))
            ->method('visitRepository')
            ->withConsecutive([$repository1], [$repository2]);
        $mockVisitor->expects($this->at(3))
            ->method('afterWorkingTree')
            ->with($this->equalTo($workingTree));

        $mockVisitor->visit($workingTree);
    }

    /**
     * @covers ::visitRepository
     */
    public function testVisitRepository() {
        $repository = new Repository('test');
        $tag1 = new Alias('1', '1.0');
        $tag2 = new Alias('2', '2.0');
        $repository->addTag($tag2);
        $repository->addTag($tag1);

        /* @var SimpleVisitor|MockObject $mockVisitor */
        $mockVisitor = $this->getMockBuilder(SimpleVisitor::class)
            ->setMethods(['beforeRepository', 'visitTag', 'afterRepository'])
            ->getMockForAbstractClass();

        $mockVisitor->expects($this->at(0))
            ->method('beforeRepository')
            ->with($this->equalTo($repository));
        $mockVisitor->expects($this->exactly(2))
            ->method('visitTag')
            ->withConsecutive([$tag1], [$tag2]);
        $mockVisitor->expects($this->at(3))
            ->method('afterRepository')
            ->with($this->equalTo($repository));

        $mockVisitor->visitRepository($repository);
    }
}
