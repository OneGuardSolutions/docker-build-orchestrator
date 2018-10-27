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
        $io = new SymfonyStyle($input, $output);
        $dirs = $input->getOption('directory');
        if (empty($dirs)) {
            $io->getErrorStyle()->writeln(
                '<comment>No root directories specified, adding current working directory.</comment>'
            );
            $dirs[] = getcwd();
        }

        $builder = new Builder();
        try {
            $workingTree = $builder->buildAll($dirs);
        } catch (NoDockerfileFoundException $e) {
            $io->writeln('<comment>No Docker images were found.</comment>');

            return 0;
        }

        $visitor = new ConsoleOutputVisitor($io);
        $visitor->visit($workingTree);
        $io->writeln('');

        if ($this->checkForBrokenAliases($io, $workingTree)) {
            return 1;
        }
        if ($this->checkForCyclicDependencies($io, $workingTree)) {
            return 2;
        }

        $repositoryCount = count($workingTree->getRepositoryNames());
        $io->success(($repositoryCount === 1 ? 'Repository is' : 'Repositories are') . ' healthy.');

        return 0;
    }

    private function checkForBrokenAliases(SymfonyStyle $io, WorkingTree $workingTree): bool {
        $brokenAliasesDetector = new BrokenAliasesDetector();
        $brokenAliases = $brokenAliasesDetector->detect($workingTree);
        if (!empty($brokenAliases)) {
            $io->error('Broken aliases detected');
            $i = 1;
            foreach ($brokenAliases as $brokenAlias) {
                $io->writeln(sprintf(
                    " %s) %s → <fg=red>%s</>",
                    $i++,
                    $brokenAlias->getFullName(),
                    $brokenAlias->getReference()
                ));
            }
            $io->writeln('');
        }

        return !empty($brokenAliases);
    }

    private function checkForCyclicDependencies(SymfonyStyle $io, WorkingTree $workingTree): bool {
        $cyclicDependenciesDetector = new CyclicDependenciesDetector();
        $cyclicDependencies = $cyclicDependenciesDetector->detect($workingTree);
        if (!empty($cyclicDependencies)) {
            $io->error('Cyclic dependencies detected');
            $i = 1;
            foreach ($cyclicDependencies as $brokenAlias) {
                $first = $brokenAlias[0];
                $io->write(sprintf(' %s) ', $i++));
                foreach ($brokenAlias as $name) {
                    $io->write(sprintf("%s\n      → ", $name));
                }
                $io->writeln($first);
                $io->writeln('');
            }
        }

        return !empty($cyclicDependencies);
    }
}
