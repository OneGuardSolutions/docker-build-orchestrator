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
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\NamedImage;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Tag;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\WorkingTree;

class CyclicDependenciesDetector {
    /**
     * Returns an array of detected cyclic dependencies, if any.
     *
     * A cyclic dependency is represented as a string array containing the chain of tag names.
     *
     * @param WorkingTree $workingTree
     * @return string[][]
     */
    public function detect(WorkingTree $workingTree): array {
        $cycles = [];

        $tags = array_filter($workingTree->getAllTags(), function (Tag $tag) {
            return $tag instanceof NamedImage;
        });
        $tags = array_map(
            function (Tag $tag) {
                return $tag->getFullName();
            },
            $tags
        );
        $visited = [];
        $stack = [];

        while (!empty($tags)) {
            $current = array_shift($tags);
            if ($current === '-') {
                array_shift($stack);
                continue;
            }

            if (in_array($current, $stack)) {
                $cycles[] = $this->constructCycleFrom($stack, $current);
                continue;
            }
            if (in_array($current, $visited)) {
                continue;
            }
            $visited[] = $current;

            $dependencies = $this->getRelevantDependencies($workingTree, $current);
            if (!empty($dependencies)) {
                $stack[] = $current;
                array_unshift($tags, '-');
                foreach ($dependencies as $dependency) {
                    array_unshift($tags, $dependency);
                }
            }
            unset($dependencies);
        }

        return $cycles;
    }

    /**
     * @param WorkingTree $workingTree
     * @param string $tagName
     * @return string[]
     */
    private function getRelevantDependencies(WorkingTree $workingTree, string $tagName): array {
        /* @var NamedImage $tag */
        $tag = $workingTree->getTag($tagName);
        $dependencies = $tag->getDependencies();

        $result = [];
        foreach ($dependencies as $dependency) {
            if (!$workingTree->hasTag($dependency)) {
                continue;
            }
            $dependencyTag = $workingTree->getTag($dependency);
            if ($dependencyTag instanceof Alias) {
                $dependencyTag = $dependencyTag->resolve();
            }
            if ($dependencyTag !== null) {
                $result[] = $dependencyTag->getFullName();
            }
        }

        return $result;
    }

    /**
     * @param array $stack
     * @param string $tag
     * @return string[]
     */
    private function constructCycleFrom(array $stack, string $tag): array {
        return array_slice($stack, array_search($tag, $stack) ?: 0);
    }
}
