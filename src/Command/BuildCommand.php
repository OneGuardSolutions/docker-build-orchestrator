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

use OneGuard\DockerBuildOrchestrator\Builder\BuildOrderProcessor;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\NamedImage;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\WorkingTree;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BuildCommand extends Command {
    use RepositoryChecksTrait;

    protected function configure() {
        $this
            ->setName('build')
            ->setDescription('Builds docker images')
            ->addArgument(
                'directory',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Root repository directory'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $style = new SymfonyStyle($input, $output);
        $workingTree = $this->buildAndVerifyWorkingTree($input->getArgument('directory'), $style);
        if (is_int($workingTree)) {
            return $workingTree;
        }

        $buildResult = $this->buildTags($workingTree, $style);

        return $buildResult ?: 0;
    }

    protected function beforeValidate(WorkingTree $workingTree, SymfonyStyle $style) {
    }

    private function buildTags(WorkingTree $workingTree, SymfonyStyle $style): int {
        $style->writeln(' Build order:');
        $orderedTags = (new BuildOrderProcessor())->determineOrder($workingTree);
        $index = 0;
        foreach ($orderedTags as $tagName) {
            $index++;
            $style->writeln("  $index) $tagName");
        }
        $style->writeln('');

        $index = 0;
        foreach ($orderedTags as $tagName) {
            $index++;
            /* @var NamedImage $tag */
            $tag = $workingTree->getTag($tagName);
            $this->buildTag($tag, $style);
            $this->pushTag($tag, $style);
        }

        $style->success('All tags built.');

        return 0;
    }

    private function buildTag(NamedImage $tag, SymfonyStyle $style) {
        $name = $tag->getFullName();
        $style->writeln(">>> Building image '$name'...");

        $cmd = sprintf(
            '%s build --pull -t "%s" "%s"',
            'docker',
            $tag->getFullName(),
            $tag->getDockerfilePath()
        );
        $style->writeln($cmd);

        $style->writeln("<<< Image '$name' was built successfully.\n");
    }

    private function pushTag(NamedImage $tag, SymfonyStyle $style) {
        $name = $tag->getFullName();
        $style->writeln(">>> Pushing image '$name'...");

        $cmd = sprintf('%s push "%s"', 'docker', $tag->getFullName());
        $style->writeln($cmd);

        $style->writeln("<<< Image '$name' was pushed successfully.\n\n");
    }
}
