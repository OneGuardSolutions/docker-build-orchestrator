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

class AliasTest extends TestCase {
    public function testGetReference() {
        $alias = new Alias('latest', '1');

        $this->assertEquals('1', $alias->getReference());
    }
}
