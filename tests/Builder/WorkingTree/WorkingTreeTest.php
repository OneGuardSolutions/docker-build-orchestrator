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

class WorkingTreeTest extends TestCase {
    public function testAddAndHasAndGetRepository() {
        $workingTree = new WorkingTree();
        $repository1 = new Repository('test-1');
        $repository2 = new Repository('test-2');

        $this->assertFalse($workingTree->hasRepository('test-1'));
        $this->assertFalse($workingTree->hasRepository('test-2'));

        $workingTree->addRepository($repository1);
        $workingTree->addRepository($repository2);

        $this->assertTrue($workingTree->hasRepository('test-1'));
        $this->assertSame($repository1, $workingTree->getRepository('test-1'));
        $this->assertSame($workingTree, $repository1->getWorkingTree());
        $this->assertTrue($workingTree->hasRepository('test-2'));
        $this->assertSame($repository2, $workingTree->getRepository('test-2'));
        $this->assertSame($workingTree, $repository2->getWorkingTree());
    }

    public function testRemoveRepository() {
        $workingTree = new WorkingTree();
        $repository = new Repository('test');
        $workingTree->addRepository($repository);

        $this->assertTrue($workingTree->hasRepository('test'));

        $workingTree->removeRepository('test');

        $this->assertFalse($workingTree->hasRepository('test'));
        $this->assertNull($repository->getWorkingTree());
    }

    public function testRemoveRepositoryNotExists() {
        $workingTree = new WorkingTree();
        $result = $workingTree->removeRepository('not-exists');

        $this->assertNull($result);
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage No repository with name 'not-exists'
     */
    public function testGetRepositoryNotExists() {
        $workingTree = new WorkingTree();
        $workingTree->getRepository('not-exists');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Repository with name 'test' already exists
     */
    public function testAddRepositoryAlreadyExists() {
        $workingTree = new WorkingTree();
        $repository1 = new Repository('test');
        $repository2 = new Repository('test');
        $workingTree->addRepository($repository1);
        $workingTree->addRepository($repository2); // this line should cause an exception
    }
}
