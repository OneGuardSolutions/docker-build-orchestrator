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

    /**
     * @covers ::hasTag
     */
    public function testHasTag() {
        $workingTree = new WorkingTree();
        $repository = new Repository('test');
        $workingTree->addRepository($repository);
        $tag = new Alias('latest', '1');
        $repository->addTag($tag);

        $this->assertTrue($workingTree->hasTag('library/test:latest'));
    }

    /**
     * @covers ::hasTag
     */
    public function testHasRepositoryNotExists() {
        $workingTree = new WorkingTree();
        $repository = new Repository('test');
        $workingTree->addRepository($repository);

        $this->assertFalse($workingTree->hasTag('library/test:latest'));
    }

    /**
     * @covers ::hasTag
     */
    public function testHasTagNotExists() {
        $workingTree = new WorkingTree();

        $this->assertFalse($workingTree->hasTag('library/test:latest'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid tag name format: ''
     * @covers ::hasTag
     */
    public function testHasTagInvalidTagName() {
        $workingTree = new WorkingTree();

        $this->assertFalse($workingTree->hasTag(''));
    }

    /**
     * ::@covers ::getTag
     */
    public function testGetTag() {
        $workingTree = new WorkingTree();
        $repository = new Repository('test');
        $workingTree->addRepository($repository);
        $tag = new Alias('latest', '1');
        $repository->addTag($tag);

        $this->assertSame($tag, $workingTree->getTag('library/test:latest'));
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage No tag with name 'library/not:exists'
     *
     * ::@covers ::getTag
     */
    public function testGetTagNotExist() {
        $workingTree = new WorkingTree();
        $workingTree->getTag('library/not:exists');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid tag name format: 'invalid'
     *
     * ::@covers ::getTag
     */
    public function testGetTagInvalidName() {
        $workingTree = new WorkingTree();
        $workingTree->getTag('invalid');
    }

    /**
     * ::@covers ::getAllTags
     */
    public function testGetAllTags() {
        $workingTree = new WorkingTree();
        $repository = new Repository('test');
        $workingTree->addRepository($repository);
        $tag1 = new Alias('latest', '1');
        $tag2 = new Alias('edge', '2');
        $repository->addTag($tag1);
        $repository->addTag($tag2);
        $repository = new Repository('test-2');
        $workingTree->addRepository($repository);
        $tag3 = new Alias('latest', '1');
        $repository->addTag($tag3);

        $this->assertEquals([$tag1, $tag2, $tag3], $workingTree->getAllTags());
    }

    /**
     * @covers ::resolveTag
     */
    public function testResolveTagAlias() {
        $workingTree = new WorkingTree();
        $repository = new Repository('test');
        $workingTree->addRepository($repository);
        $tag1 = new Alias('latest', '1');
        $tag2 = new TestTag('1');
        $repository->addTag($tag1);
        $repository->addTag($tag2);

        $this->assertSame($tag2, $workingTree->resolveTag('library/test:latest'));
    }

    /**
     * @covers ::resolveTag
     */
    public function testResolveTagNotAlias() {
        $workingTree = new WorkingTree();
        $repository = new Repository('test');
        $workingTree->addRepository($repository);
        $tag = new TestTag('1');
        $repository->addTag($tag);

        $this->assertSame($tag, $workingTree->resolveTag('library/test:1'));
    }

    /**
     * @covers ::resolveTag
     */
    public function testResolveTagBroken() {
        $workingTree = new WorkingTree();
        $repository = new Repository('test');
        $workingTree->addRepository($repository);
        $tag = new Alias('latest', '1');
        $repository->addTag($tag);

        $this->assertNull($workingTree->resolveTag('library/test:latest'));
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage No tag with name 'test/not:exists'
     * @covers ::resolveTag
     */
    public function testResolveTagNotExists() {
        $workingTree = new WorkingTree();

        $this->assertNull($workingTree->resolveTag('test/not:exists'));
    }
}
