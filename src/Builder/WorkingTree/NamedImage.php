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

    /**
     * @var string[]|null
     */
    private $dependencies;

    public function __construct(string $name, string $dockerfilePath) {
        parent::__construct($name);
        $this->dockerfilePath = $dockerfilePath;

        if (is_file($dockerfilePath)) {
            $source = explode("\n", file_get_contents($dockerfilePath));
            $this->dependencies = [];
            foreach ($source as $line) {
                $line = trim($line);
                $matches = [];
                if (preg_match('/^FROM +([^\s]+)( +AS .*)?$/', $line, $matches)) {
                    $dependency = $matches[1];
                    if (strpos($dependency, ':') === false) {
                        $dependency .= ':latest';
                    }
                    if (!in_array($dependency, $this->dependencies)) {
                        $this->dependencies[] = $dependency;
                    }
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getDockerfilePath(): string {
        return $this->dockerfilePath;
    }

    /**
     * @return string[]
     */
    public function getDependencies(): ?array {
        return $this->dependencies;
    }
}
