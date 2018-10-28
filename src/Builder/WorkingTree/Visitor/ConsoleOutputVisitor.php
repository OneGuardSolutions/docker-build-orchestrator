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

use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Alias;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\NamedImage;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Repository;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Tag;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutputVisitor extends SimpleVisitor {
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output) {
        $this->output = $output;
    }

    protected function beforeRepository(Repository $repository) {
        $this->output->writeln(sprintf(' - %s', $repository->getFullName()));
    }

    public function visitTag(Tag $tag) {
        if ($tag instanceof Alias) {
            $this->visitAlias($tag);
        } elseif ($tag instanceof NamedImage) {
            $this->visitNamedImage($tag);
        } else {
            $this->output->writeln(sprintf("     ↳ %s", $tag->getName()));
        }
    }

    public function visitAlias(Alias $alias) {
        $reference = $alias->getReference();
        if ($alias->isBroken()) {
            $reference = sprintf('<fg=red>%s</>', $reference);
        } else {
            $reference = $this->getReferenceForNonBrokenAlias($alias);
        }
        $this->output->writeln(sprintf('     ↳ <fg=cyan>%s</> → %s', $alias->getName(), $reference));
    }

    public function visitNamedImage(NamedImage $namedImage) {
        $dockerfilePath = $namedImage->getDockerfilePath();
        if (!is_file($dockerfilePath)) {
            $dockerfilePath = sprintf('<fg=red>%s</>', $dockerfilePath);
        }
        $this->output->writeln(sprintf('     ↳ <info>%s</info> → %s', $namedImage->getName(), $dockerfilePath));
        if ($namedImage->getDependencies() !== null) {
            foreach ($namedImage->getDependencies() as $dependency) {
                $this->output->writeln(sprintf(
                    '         - depends on: %s%s',
                    $dependency,
                    $this->isInternal($namedImage, $dependency) ? '' : ' (external)'
                ));
            }
        }
    }

    private function getReferenceForNonBrokenAlias(Alias $alias) {
        $repository = $alias->getRepository();
        $tag = $repository->getTag($alias->getReference());
        if (!($tag instanceof Alias)) {
            return sprintf('<info>%s</info>', $alias->getReference());
        }

        $resolved = $tag->resolve();

        return sprintf(
            '<fg=cyan>%s</> (%s)',
            $alias->getReference(),
            $resolved === null ? '<fg=red>unresolved</>' : sprintf('<info>%s</info>', $resolved->getName())
        );
    }

    private function isInternal(Tag $source, string $dependency): bool {
        $repository = $source->getRepository();
        if ($repository === null) {
            return false;
        }
        [$repositoryName, $tagName] = explode(':', $dependency, 2);
        if ($repositoryName === $repository->getFullName()) {
            return $repository->hasTag($tagName);
        }

        $workingTree = $repository->getWorkingTree();

        return $workingTree !== null && $workingTree->hasTag($dependency);
    }
}
