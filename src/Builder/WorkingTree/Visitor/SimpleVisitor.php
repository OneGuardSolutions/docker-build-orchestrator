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

use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Repository;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Tag;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\WorkingTree;
use OneGuard\DockerBuildOrchestrator\Utils\RepositoryUtils;

abstract class SimpleVisitor implements VisitorInterface {
    public function visit(WorkingTree $workingTree) {
        $repositoryNames = $workingTree->getRepositoryNames();
        usort($repositoryNames, [RepositoryUtils::class, 'fullNameComparator']);

        $this->beforeWorkingTree($workingTree);
        foreach ($repositoryNames as $repositoryName) {
            $repository = $workingTree->getRepository($repositoryName);
            $this->visitRepository($repository);
        }
        $this->afterWorkingTree($workingTree);
    }

    public function visitRepository(Repository $repository) {
        $tagNames = $repository->getTagNames();
        sort($tagNames, SORT_ASC | SORT_NUMERIC);

        $this->beforeRepository($repository);
        foreach ($tagNames as $tagName) {
            $tag = $repository->getTag($tagName);
            $this->visitTag($tag);
        }
        $this->afterRepository($repository);
    }

    abstract public function visitTag(Tag $tag);

    protected function beforeWorkingTree(WorkingTree $workingTree) {
    }

    protected function afterWorkingTree(WorkingTree $workingTree) {
    }

    protected function beforeRepository(Repository $repository) {
    }

    protected function afterRepository(Repository $repository) {
    }
}
