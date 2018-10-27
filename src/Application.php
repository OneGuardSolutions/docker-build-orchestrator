<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OneGuard\DockerBuildOrchestrator;

use Deployer\Component\PharUpdate\Console\Command;
use Deployer\Component\PharUpdate\Console\Helper;
use OneGuard\DockerBuildOrchestrator\Command\CheckCommand;
use OneGuard\DockerBuildOrchestrator\Utils\VersionUtils;

class Application extends \Symfony\Component\Console\Application {
    public function __construct($version) {
        parent::__construct('dobr', VersionUtils::normalize($version));

        $this->getHelperSet()->set(new Helper());

        $this->add(new CheckCommand());

        $updateCommand = new Command('update');
        $updateCommand->setManifestUri('@manifest_url@');
        $this->add($updateCommand);
    }
}
