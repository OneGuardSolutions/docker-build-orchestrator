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
        } else if ($tag instanceof NamedImage) {
            $this->visitNamedImage($tag);
        } else {
            $this->output->writeln(sprintf("     ↳ %s", $tag->getName()));
        }
    }

    public function visitAlias(Alias $alias) {
        $reference = $alias->getReference();
        $repository = $alias->getRepository();
        if ($repository === null || !$repository->hasTag($alias->getReference())) {
            $reference = sprintf('<fg=red>%s</>', $reference);
        } else {
            $tag = $repository->getTag($reference);
            if ($tag instanceof NamedImage) {
                $reference = sprintf('<info>%s</info>', $reference);
            } else if ($tag instanceof Alias) {
                $resolvedReference = null;
                $resolved = $tag->resolve();
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
}
