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

use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Visitor\ConsoleOutputVisitor;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\WorkingTree;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckCommand extends Command {
    use RepositoryChecksTrait;

    protected function configure() {
        $this
            ->setName('check')
            ->setDescription('Checks health status of detected repositories repositories and images')
            ->addArgument(
                'directory',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Root repository directory'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $workingTree = $this->buildAndVerifyWorkingTree(
            $input->getArgument('directory'),
            new SymfonyStyle($input, $output)
        );
        if (is_int($workingTree)) {
            return $workingTree;
        }

        $repositoryCount = count($workingTree->getRepositoryNames());
        $style = new SymfonyStyle($input, $output);
        $style->success(($repositoryCount === 1 ? 'Repository is' : 'Repositories are') . ' healthy.');

        return 0;
    }

    protected function beforeValidate(WorkingTree $workingTree, SymfonyStyle $style) {
        $visitor = new ConsoleOutputVisitor($style);
        $visitor->visit($workingTree);
    }
}
