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
 * @coversDefaultClass \OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Alias
 */
class AliasTest extends TestCase {
    /**
     * @covers ::__construct
     * @covers ::getReference
     */
    public function testGetReference() {
        $alias = new Alias('latest', '1');

        $this->assertEquals('1', $alias->getReference());
    }

    /**
     * @covers ::resolve
     */
    public function testResolve() {
        $repository = new Repository('test');
        $alias1 = new Alias('latest', '1');
        $alias2 = new Alias('1', '1.0');
        $alias3 = new Alias('1.0', '1.0.0');
        $namedImage = new NamedImage(
            '1.0.0',
            __DIR__ . '/../_resources/docker/repositories-1/test/1.2.3/Dockerfile'
        );
        $repository->addTag($namedImage);
        $repository->addTag($alias3);
        $repository->addTag($alias2);
        $repository->addTag($alias1);

        $this->assertSame($namedImage, $alias1->resolve());
    }

    /**
     * @covers ::resolve
     */
    public function testResolveNull() {
        $repository = new Repository('test');
        $alias = new Alias('latest', '1');
        $repository->addTag($alias);

        $this->assertNull($alias->resolve());
    }

    /**
     * @covers ::resolve
     */
    public function testResolveNoRepository() {
        $alias = new Alias('latest', '1');

        $this->assertNull($alias->resolve());
    }

    /**
     * @covers ::isBroken
     */
    public function testIsBrokenNoRepository() {
        $alias = new Alias('latest', '1');

        $this->assertTrue($alias->isBroken());
    }

    /**
     * @covers ::isBroken
     */
    public function testIsBrokenBrokenReference() {
        $alias = new Alias('latest', '1');
        $alias->setRepository(new Repository('test'));

        $this->assertTrue($alias->isBroken());
    }

    /**
     * @covers ::isBroken
     */
    public function testIsBrokenCorrectReference() {
        $repository = new Repository('test');
        $alias = new Alias('latest', '1');
        $repository->addTag($alias);
        $repository->addTag(new Alias('1', '1.0'));

        $this->assertFalse($alias->isBroken());
    }
}
