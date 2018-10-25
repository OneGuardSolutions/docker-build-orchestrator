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

class WorkingTree {
    /**
     * @var Repository[]
     */
    private $repositories = [];

    /**
     * @return string[]
     */
    public function getRepositoryNames(): array {
        return array_keys($this->repositories);
    }

    public function hasRepository(string $name): bool {
        return isset($this->repositories[$name]);
    }

    /**
     * @param string $name
     * @return Repository
     * @throws \OutOfBoundsException if no {@link Repository} with specified name is found
     */
    public function getRepository(string $name): Repository {
        if (!$this->hasRepository($name)) {
            throw new \OutOfBoundsException("No repository with name '$name'");
        }

        return $this->repositories[$name];
    }

    /**
     * Register the {@link Repository} with the {@link WorkingTree}
     * if a {@link Repository} with the same name is not registered yet.
     *
     * @param Repository $repository
     * @throws \InvalidArgumentException if a {@link Repository} with same name is already registered in the {@link WorkingTree}
     */
    public function addRepository(Repository $repository): void {
        $name = $repository->getName();
        if ($this->hasRepository($name)) {
            throw new \InvalidArgumentException("Repository with name '$name' already exists");
        }

        $this->repositories[$name] = $repository;
        if ($repository->getWorkingTree() !== $this) {
            $repository->setWorkingTree($this);
        }
    }

    public function removeRepository(string $name): ?Repository {
        if (!$this->hasRepository($name)) {
            return null;
        }

        $repository = $this->repositories[$name];
        unset($this->repositories[$name]);
        if ($repository->getWorkingTree() === $this) {
            $repository->setWorkingTree(null);
        }

        return $repository;
    }
}
