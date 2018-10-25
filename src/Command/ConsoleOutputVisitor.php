<?php

namespace OneGuard\DockerBuildOrchestrator\Command;

use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Alias;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\NamedImage;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Repository;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Tag;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Visitor\VisitorInterface;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\WorkingTree;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutputVisitor implements VisitorInterface {
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output) {
        $this->output = $output;
    }

    public function visitWorkingTree(WorkingTree $workingTree) {
        $repositoryNames = $workingTree->getRepositoryNames();
        sort($repositoryNames, SORT_ASC);

        foreach ($repositoryNames as $repositoryName) {
            $repository = $workingTree->getRepository($repositoryName);
            $this->visitRepository($repository);
        }
    }

    public function visitRepository(Repository $repository) {
        $tagNames = $repository->getTagNames();
        sort($tagNames, SORT_ASC);

        $this->output->writeln(sprintf(' - %s', $repository->getFullName()));
        foreach ($tagNames as $tagName) {
            $tag = $repository->getTag($tagName);
            $this->visitTag($tag);
        }
    }

    public function visitTag(Tag $tag) {
        if ($tag instanceof Alias) {
            $this->visitAlias($tag);
        } else if ($tag instanceof NamedImage) {
            $this->visitNamedImage($tag);
        } else {
            $this->output->write(sprintf("     ↳ %s", $tag->getName()));
        }
    }

    public function visitAlias(Alias $alias) {
        $reference = $alias->getReference();
        $repository = $alias->getRepository();
        if (!$repository->hasTag($alias->getReference())) {
            $reference = sprintf('<fg=red>%s</>', $reference);
        } else {
            $tag = $repository->getTag($reference);
            if ($tag instanceof NamedImage) {
                $reference = sprintf('<info>%s</info>', $reference);
            } else if ($tag instanceof Alias) {
                $resolvedReference = null;
                $resolved = $this->resolve($tag);
                if ($resolved === null) {
                    $resolvedReference = '<fg=red>unresolved</>';
                } else {
                    $resolvedReference = sprintf('<info>%s</info>', $resolved->getName());
                }
                $reference = sprintf('<fg=cyan>%s</> (%s)', $reference, $resolvedReference);
            }
        }
        $this->output->writeln(sprintf('     ↳ <fg=cyan>%s</> → %s', $alias->getName(), $reference));
    }

    public function visitNamedImage(NamedImage $namedImage) {
        $dockerfilePath = $namedImage->getDockerfilePath();
        if (!is_file($dockerfilePath)) {
            $dockerfilePath = sprintf('<fg=red>%s</>', $dockerfilePath);
        }
        $this->output->writeln(sprintf(
            '     ↳ <info>%s</info> → %s',
            $namedImage->getName(),
            $dockerfilePath
        ));
    }

    private function resolve(Alias $alias): ?Tag {
        $tag = $alias;
        while ($tag instanceof Alias) {
            $repository = $tag->getRepository();
            $tag = $repository->hasTag($tag->getReference()) ? $repository->getTag($tag->getReference()) : null;
        }

        return $tag;
    }
}