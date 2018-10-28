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
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Visitor\ConsoleOutputVisitor;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\WorkingTree;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckCommand extends Command {
    protected function configure() {
        $this
            ->setName('check')
            ->setDescription('Checks health status of detected repositories repositories and images')
            ->addOption(
                'directory',
                'd',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Root repository directory'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $style = new SymfonyStyle($input, $output);
        $dirs = $input->getOption('directory');
        if (empty($dirs)) {
            $style->getErrorStyle()->writeln(
                '<comment>No root directories specified, adding current working directory.</comment>'
            );
            $dirs[] = getcwd();
        }

        $builder = new Builder();
        try {
            $workingTree = $builder->buildAll($dirs);
        } catch (NoDockerfileFoundException $e) {
            $style->writeln('<comment>No Docker images were found.</comment>');

            return 0;
        }

        $visitor = new ConsoleOutputVisitor($style);
        $visitor->visit($workingTree);
        $style->writeln('');

        if ($this->checkForBrokenAliases($style, $workingTree)) {
            return 1;
        }
        if ($this->checkForCyclicDependencies($style, $workingTree)) {
            return 2;
        }

        $repositoryCount = count($workingTree->getRepositoryNames());
        $style->success(($repositoryCount === 1 ? 'Repository is' : 'Repositories are') . ' healthy.');

        return 0;
    }

    private function checkForBrokenAliases(SymfonyStyle $style, WorkingTree $workingTree): bool {
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

    private function checkForCyclicDependencies(SymfonyStyle $style, WorkingTree $workingTree): bool {
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
