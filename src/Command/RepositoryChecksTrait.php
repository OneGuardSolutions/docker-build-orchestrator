<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OneGuard\DockerBuildOrchestrator\Command;

use OneGuard\DockerBuildOrchestrator\Builder\BrokenAliasesDetector;
use OneGuard\DockerBuildOrchestrator\Builder\Builder;
use OneGuard\DockerBuildOrchestrator\Builder\CyclicDependenciesDetector;
use OneGuard\DockerBuildOrchestrator\Builder\NoDockerfileFoundException;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\WorkingTree;
use Symfony\Component\Console\Style\SymfonyStyle;

trait RepositoryChecksTrait {
    /**
     * @param string[] $dirs
     * @param SymfonyStyle $style
     * @return int|WorkingTree
     */
    protected function buildAndVerifyWorkingTree(array $dirs, SymfonyStyle $style) {
        $builder = new Builder();
        try {
            $workingTree = $builder->buildAll($dirs);
        } catch (NoDockerfileFoundException $e) {
            $style->writeln('<comment>No Docker images were found.</comment>');

            return 1;
        }

        $this->beforeValidate($workingTree, $style);
        $validationResult = $this->validate($workingTree, $style);

        return $validationResult === 0 ? $workingTree : $validationResult;
    }

    abstract protected function beforeValidate(WorkingTree $workingTree, SymfonyStyle $style);

    protected function validate(WorkingTree $workingTree, SymfonyStyle $style) {
        if ($this->checkForBrokenAliases($workingTree, $style)) {
            return 2;
        }
        if ($this->checkForCyclicDependencies($workingTree, $style)) {
            return 3;
        }

        return 0;
    }

    private function checkForBrokenAliases(WorkingTree $workingTree, SymfonyStyle $style): bool {
        $detector = new BrokenAliasesDetector();
        $brokenAliases = $detector->detect($workingTree);
        if (!empty($brokenAliases)) {
            $style->error('Broken aliases detected');
            $index = 1;
            foreach ($brokenAliases as $brokenAlias) {
                $style->writeln(sprintf(
                    " %s) %s → <fg=red>%s</>",
                    $index++,
                    $brokenAlias->getFullName(),
                    $brokenAlias->getReference()
                ));
            }
            $style->writeln('');
        }

        return !empty($brokenAliases);
    }

    private function checkForCyclicDependencies(WorkingTree $workingTree, SymfonyStyle $style): bool {
        $detector = new CyclicDependenciesDetector();
        $cyclicDependencies = $detector->detect($workingTree);
        if (!empty($cyclicDependencies)) {
            $style->error('Cyclic dependencies detected');
            $index = 1;
            foreach ($cyclicDependencies as $brokenAlias) {
                $first = $brokenAlias[0];
                $style->write(sprintf(' %s) ', $index++));
                foreach ($brokenAlias as $name) {
                    $style->write(sprintf("%s\n      → ", $name));
                }
                $style->writeln($first);
                $style->writeln('');
            }
        }

        return !empty($cyclicDependencies);
    }
}
