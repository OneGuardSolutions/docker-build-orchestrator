<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OneGuard\DockerBuildOrchestrator\Builder;

use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Alias;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Tag;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\WorkingTree;

class BrokenAliasesDetector {
    /**
     * Returns an array of detected broken aliases, if any.
     *
     * A cyclic dependency is represented as a string array containing the chain of tag names.
     *
     * @param WorkingTree $workingTree
     * @return Alias[]
     */
    public function detect(WorkingTree $workingTree): array {
        $brokenAliases = array_filter(
            $workingTree->getAllTags(),
            function (Tag $tag) use ($workingTree) {
                if (!($tag instanceof Alias)) {
                    return false;
                }

                return !$tag->getRepository()->hasTag($tag->getReference());
            }
        );
        sort($brokenAliases);

        return $brokenAliases;
    }
}
