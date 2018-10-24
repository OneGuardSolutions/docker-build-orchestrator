<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use OneGuard\DockerBuildOrchestrator\Command\BuildCommand;
use OneGuard\DockerBuildOrchestrator\Utils\VersionUtils;
use Deployer\Component\PharUpdate\Console\Command;
use Deployer\Component\PharUpdate\Console\Helper;

class Application extends \Symfony\Component\Console\Application {
    public function __construct($version) {
        parent::__construct('dobr', (new VersionUtils())->normalize($version));

        $this->getHelperSet()->set(new Helper());

        $this->add(new BuildCommand());

        $updateCommand = new Command('update');
        $updateCommand->setManifestUri('@manifest_url@');
        $this->add($updateCommand);
    }
}