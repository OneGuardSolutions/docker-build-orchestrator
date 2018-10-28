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

use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Tag;

class TestSimpleVisitor extends SimpleVisitor {
    public function visitTag(Tag $tag) {
    }
}
