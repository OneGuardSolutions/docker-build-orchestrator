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
     * @var string[]
     */
    private $dependencies = [];

    public function __construct(string $name, string $dockerfilePath) {
        parent::__construct($name);
        $this->dockerfilePath = $dockerfilePath;
        if (is_file($dockerfilePath)) {
            $this->detectDependencies($dockerfilePath);
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
    public function getDependencies(): array {
        return $this->dependencies;
    }

    private function detectDependencies(string $dockerfilePath): void {
        $source = explode("\n", file_get_contents($dockerfilePath));
        $this->dependencies = [];
        foreach ($source as $line) {
            $dependency = $this->extractDependencyFromLine(trim($line));
            if ($dependency !== null && !in_array($dependency, $this->dependencies)) {
                $this->dependencies[] = $dependency;
            }
        }
    }

    private function extractDependencyFromLine(string $line): ?string {
        $matches = [];
        if (preg_match('/^FROM +([^\s]+)( +AS .*)?$/', $line, $matches)) {
            return strpos($matches[1], ':') === false ? $matches[1] . ':latest' : $matches[1];
        }

        return null;
    }
}
