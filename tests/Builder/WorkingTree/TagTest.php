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
 * @coversDefaultClass \OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Tag
 */
class TagTest extends TestCase {
    /**
     * @covers ::__construct
     * @covers ::getName
     */
    public function testGetName() {
        $tag = new TestTag('test');

        $this->assertEquals('test', $tag->getName());
    }

    /**
     * @covers ::getRepository
     * @covers ::setRepository
     */
    public function testSetAndGetRepository() {
        $repository = new Repository('test');
        $tag = new TestTag('1');
        $tag->setRepository($repository);

        $this->assertSame($repository, $tag->getRepository());
        $this->assertSame($tag, $repository->getTag('1'));
    }

    /**
     * @covers ::setRepository
     */
    public function testSetRepositoryUnset() {
        $repository = new Repository('test');
        $tag = new TestTag('1');
        $tag->setRepository($repository);
        $tag->setRepository(null);

        $this->assertSame(null, $tag->getRepository());
        $this->assertFalse($repository->hasTag('1'));
    }

    /**
     * @covers ::setRepository
     */
    public function testSetRepositorySame() {
        $repository = new Repository('test');
        $tag = new TestTag('1');
        $tag->setRepository($repository);
        $tag->setRepository($repository);

        $this->assertSame($repository, $tag->getRepository());
        $this->assertSame($tag, $repository->getTag('1'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Tag with name '1' already exists
     * @covers ::setRepository
     */
    public function testSetRepositoryWithSuchTagNoReplace() {
        $repository = new Repository('test');
        $tag1 = new TestTag('1');
        $tag2 = new TestTag('1');
        $repository->addTag($tag1);

        $tag2->setRepository($repository);
    }

    /**
     * @covers ::getFullName
     */
    public function testGetFullName() {
        $repository = new Repository('test', 'tester', 'test.docker.io');
        $tag = new TestTag('1');
        $repository->addTag($tag);

        $this->assertEquals('test.docker.io/tester/test:1', $tag->getFullName());
    }

    /**
     * @covers ::getFullName
     */
    public function testGetFullNameNoRepository() {
        $tag = new TestTag('1');

        $this->assertEquals('1', $tag->getFullName());
    }
}
