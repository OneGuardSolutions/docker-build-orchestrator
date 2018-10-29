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
use OneGuard\DockerBuildOrchestrator\Utils\RepositoryUtils;

class BuildOrderProcessor {
    /**
     * @param WorkingTree $workingTree
     * @return string[]
     */
    public function determineOrder(WorkingTree $workingTree): array {
        $tags = $this->getTags($workingTree);
        $order = [];
        $stack = [];

        while (!empty($tags)) {
            $this->addDependencies($workingTree, $tags, $order, $stack);
        }

        return $order;
    }

    private function addDependencies(WorkingTree $workingTree, array &$tags, array &$order, array &$stack) {
        $current = array_shift($tags);
        if ($current === '-') {
            $order[] = array_shift($stack);
            return;
        }

        if (in_array($current, $order)) {
            return;
        }

        $dependencies = $this->getRelevantDependencies($workingTree, $current);
        if (!empty($dependencies)) {
            $stack[] = $current;
            array_unshift($tags, '-');
            foreach ($dependencies as $dependency) {
                array_unshift($tags, $dependency);
            }
        } else {
            $order[] = $current;
        }
    }

    /**
     * @param WorkingTree $workingTree
     * @return string[]
     */
    private function getTags(WorkingTree $workingTree): array {
        $tags = array_filter($workingTree->getAllTags(), function (Tag $tag) {
            return $tag instanceof NamedImage;
        });

        $tags = array_map(
            function (Tag $tag) {
                return $tag->getFullName();
            },
            $tags
        );
        usort($tags, [RepositoryUtils::class, 'tagNameComparator']);

        return $tags;
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
}
