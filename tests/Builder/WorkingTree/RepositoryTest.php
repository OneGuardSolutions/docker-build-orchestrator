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

class RepositoryTest extends TestCase {
    public function testGetName() {
        $repository = new Repository('test');

        $this->assertEquals('test', $repository->getName());
        $this->assertEquals('library', $repository->getNamespace());
        $this->assertEquals('', $repository->getRegistry());
    }

    public function testGetNamespace() {
        $repository = new Repository('test', 'test-namespace');

        $this->assertEquals('test-namespace', $repository->getNamespace());
    }

    public function testGetRegistry() {
        $repository = new Repository('test', 'library', 'test.registry.io');

        $this->assertEquals('test.registry.io', $repository->getRegistry());
    }

    public function testAddAndHasAndGetTag() {
        $repository = new Repository('test');
        $tag1 = new Tag('1');
        $tag2 = new Tag('2');

        $this->assertFalse($repository->hasTag('1'));
        $this->assertFalse($repository->hasTag('2'));

        $repository->addTag($tag1);
        $repository->addTag($tag2);

        $this->assertTrue($repository->hasTag('1'));
        $this->assertSame($tag1, $repository->getTag('1'));
        $this->assertSame($repository, $tag1->getRepository());
        $this->assertTrue($repository->hasTag('2'));
        $this->assertSame($tag2, $repository->getTag('2'));
        $this->assertSame($repository, $tag2->getRepository());
    }

    public function testRemoveTag() {
        $repository = new Repository('test');
        $tag = new Tag('1');
        $repository->addTag($tag);

        $this->assertTrue($repository->hasTag('1'));
        $this->assertSame($repository, $tag->getRepository());

        $repository->removeTag('1');

        $this->assertFalse($repository->hasTag('1'));
        $this->assertNull($tag->getRepository());
    }

    public function testRemoveTagNotExists() {
        $repository = new Repository('test');
        $result = $repository->removeTag('not-exists');

        $this->assertNull($result);
    }

    public function testGetTagNames() {
        $repository = new Repository('test');
        $repository->addTag(new Tag('test-1'));
        $repository->addTag(new Tag('test-2'));
        $repository->addTag(new Tag('test-3'));

        $this->assertEquals(['test-1', 'test-2', 'test-3'], $repository->getTagNames());
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage No tag with name 'not-exists'
     */
    public function testGetTagNotExists() {
        $repository = new Repository('test');
        $repository->getTag('not-exists');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Tag with name '1' already exists
     */
    public function testAddTagAlreadyExists() {
        $repository = new Repository('test');
        $tag1 = new Tag('1');
        $tag2 = new Tag('1');
        $repository->addTag($tag1);
        $repository->addTag($tag2); // this line should cause an exception
    }

    public function testSetAndGetWorkingTree() {
        $workingTree = new WorkingTree();
        $repository = new Repository('test');
        $repository->setWorkingTree($workingTree);

        $this->assertSame($workingTree, $repository->getWorkingTree());
        $this->assertSame($repository, $workingTree->getRepository('test'));
    }

    public function testSetWorkingTreeUnset() {
        $workingTree = new WorkingTree();
        $repository = new Repository('test');
        $repository->setWorkingTree($workingTree);
        $repository->setWorkingTree(null);

        $this->assertSame(null, $repository->getWorkingTree());
        $this->assertFalse($workingTree->hasRepository('test'));
    }

    public function testSetWorkingTreeSame() {
        $workingTree = new WorkingTree();
        $repository = new Repository('test');
        $repository->setWorkingTree($workingTree);
        $repository->setWorkingTree($workingTree);

        $this->assertSame($workingTree, $repository->getWorkingTree());
        $this->assertSame($repository, $workingTree->getRepository('test'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Repository with name 'test' already exists
     */
    public function testSetWorkingTreeWithSuchRepositoryNoReplace() {
        $workingTree = new WorkingTree();
        $repository1 = new Repository('test');
        $repository2 = new Repository('test');
        $workingTree->addRepository($repository1);

        $repository2->setWorkingTree($workingTree);
    }

    public function testGetFullName() {
        $repository = new Repository('test', 'tester', 'test.docker.io');

        $this->assertEquals('', $repository->getFullName());
    }
}
