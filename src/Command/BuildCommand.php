<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends Command {
    protected function configure() {
        $this
            ->setName('build')
            ->setDescription('Say hello')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('Building...');
    }
}
