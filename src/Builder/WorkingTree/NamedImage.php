<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OneGuard\DockerBuildOrchestrator\Builder\WorkingTree;

class NamedImage extends Tag {
    /**
     * @var string
     */
    private $dockerfilePath;

    public function __construct(string $name, string $dockerfilePath) {
        parent::__construct($name);
        $this->dockerfilePath = $dockerfilePath;
    }

    /**
     * @return string
     */
    public function getDockerfilePath(): string {
        return $this->dockerfilePath;
    }
}
