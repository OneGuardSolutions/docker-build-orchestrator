<?php

namespace OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Visitor;

use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Repository;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Tag;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\WorkingTree;

class ConsoleOutputVisitor implements VisitorInterface {
    function visitWorkingTree(WorkingTree $workingTree) {
        $repositoryNames = $workingTree->getRepositoryNames();
        sort($repositoryNames, SORT_ASC);

        foreach ($repositoryNames as $repositoryName) {
            $repository = $workingTree->getRepository($repositoryName);
            $this->visitRepository($repository);
        }
    }

    function visitRepository(Repository $repository) {
        $tagNames = $repository->getTagNames();
        sort($tagNames, SORT_ASC);

        printf(" - %s\n", $repository->getFullName());
        foreach ($tagNames as $tagName) {
            $tag = $repository->getTag($tagName);
            $this->visitTag($tag);
        }
    }

    function visitTag(Tag $tag) {
        printf("     ↳ %s\n", $tag->getFullName());
    }
}
