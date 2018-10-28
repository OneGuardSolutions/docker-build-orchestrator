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

        $tags = $this->getTags($workingTree);
        $visited = [];
        $stack = [];

        while (!empty($tags)) {
            $this->detectCyclicDependency($workingTree, $tags, $visited, $stack, $cycles);
        }

        return $cycles;
    }

    private function detectCyclicDependency(WorkingTree $workingTree, array &$tags, array &$visited, array &$stack, array &$cycles) {
        $current = array_shift($tags);
        if ($current === '-') {
            array_shift($stack);
            return;
        }

        if (in_array($current, $stack)) {
            $cycles[] = $this->constructCycleFrom($stack, $current);
            return;
        }
        if (in_array($current, $visited)) {
            return;
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
    }

    private function getTags(WorkingTree $workingTree): array {
        $tags = array_filter($workingTree->getAllTags(), function (Tag $tag) {
            return $tag instanceof NamedImage;
        });

        return array_map(
            function (Tag $tag) {
                return $tag->getFullName();
            },
            $tags
        );
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
            $dependencyTag = $workingTree->resolveTag($dependency);
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
