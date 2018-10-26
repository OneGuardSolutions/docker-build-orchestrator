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
 * @coversDefaultClass \OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\WorkingTree
 */
class WorkingTreeTest extends TestCase {
    /**
     * @covers ::hasRepository
     * @covers ::addRepository
     * @covers ::getRepository
     */
    public function testAddAndHasAndGetRepository() {
        $workingTree = new WorkingTree();
        $repository1 = new Repository('test-1');
        $repository2 = new Repository('test-2');

        $this->assertFalse($workingTree->hasRepository('test-1'));
        $this->assertFalse($workingTree->hasRepository('test-2'));

        $workingTree->addRepository($repository1);
        $workingTree->addRepository($repository2);

        $this->assertTrue($workingTree->hasRepository('library/test-1'));
        $this->assertSame($repository1, $workingTree->getRepository('library/test-1'));
        $this->assertSame($workingTree, $repository1->getWorkingTree());
        $this->assertTrue($workingTree->hasRepository('library/test-2'));
        $this->assertSame($repository2, $workingTree->getRepository('library/test-2'));
        $this->assertSame($workingTree, $repository2->getWorkingTree());
    }

    /**
     * @covers ::removeRepository
     */
    public function testRemoveRepository() {
        $workingTree = new WorkingTree();
        $repository = new Repository('test');
        $workingTree->addRepository($repository);

        $this->assertTrue($workingTree->hasRepository('library/test'));

        $workingTree->removeRepository('library/test');

        $this->assertFalse($workingTree->hasRepository('library/test'));
        $this->assertNull($repository->getWorkingTree());
    }

    /**
     * @covers ::removeRepository
     */
    public function testRemoveRepositoryNotExists() {
        $workingTree = new WorkingTree();
        $result = $workingTree->removeRepository('not-exists');

        $this->assertNull($result);
    }

    /**
     * @covers ::getRepositoryNames
     */
    public function testGetRepositoryNames() {
        $workingTree = new WorkingTree();
        $workingTree->addRepository(new Repository('test-1'));
        $workingTree->addRepository(new Repository('test-2', 'test-lib'));
        $workingTree->addRepository(new Repository('test-3', 'abc', 'docker.io'));

        $this->assertEquals(['library/test-1', 'test-lib/test-2', 'docker.io/abc/test-3'], $workingTree->getRepositoryNames());
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage No repository with name 'not-exists'
     * @covers ::getRepository
     */
    public function testGetRepositoryNotExists() {
        $workingTree = new WorkingTree();
        $workingTree->getRepository('not-exists');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Repository with name 'library/test' already exists
     * @covers ::addRepository
     */
    public function testAddRepositoryAlreadyExists() {
        $workingTree = new WorkingTree();
        $repository1 = new Repository('test');
        $repository2 = new Repository('test');
        $workingTree->addRepository($repository1);
        $workingTree->addRepository($repository2); // this line should cause an exception
    }
}
