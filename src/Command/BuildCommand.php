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

use OneGuard\DockerBuildOrchestrator\Builder\Builder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BuildCommand extends Command {
    protected function configure() {
        $this
            ->setName('build')
            ->setDescription('Say hello')
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
        $workingTree = $builder->buildAll($dirs);

        (new ConsoleOutputVisitor($io))->visit($workingTree);

        return 0;
    }
}
