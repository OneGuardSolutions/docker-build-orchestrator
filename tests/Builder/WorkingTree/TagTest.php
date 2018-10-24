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

class TagTest extends TestCase {
    public function testGetName() {
        $tag = new Tag('test');

        $this->assertEquals('test', $tag->getName());
    }

    public function testSetAndGetRepository() {
        $repository = new Repository('test');
        $tag = new Tag('1');
        $tag->setRepository($repository);

        $this->assertSame($repository, $tag->getRepository());
        $this->assertSame($tag, $repository->getTag('1'));
    }

    public function testSetRepositoryUnset() {
        $repository = new Repository('test');
        $tag = new Tag('1');
        $tag->setRepository($repository);
        $tag->setRepository(null);

        $this->assertSame(null, $tag->getRepository());
        $this->assertFalse($repository->hasTag('1'));
    }

    public function testSetRepositorySame() {
        $repository = new Repository('test');
        $tag = new Tag('1');
        $tag->setRepository($repository);
        $tag->setRepository($repository);

        $this->assertSame($repository, $tag->getRepository());
        $this->assertSame($tag, $repository->getTag('1'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Tag with name '1' already exists
     */
    public function testSetRepositoryWithSuchTagNoReplace() {
        $repository = new Repository('test');
        $tag1 = new Tag('1');
        $tag2 = new Tag('1');
        $repository->addTag($tag1);

        $tag2->setRepository($repository);
    }
}
