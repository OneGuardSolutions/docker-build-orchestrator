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
    public function getRepositoryNames() {
        return array_map(
            function (Repository $repository) {
                return $repository->getName();
            },
            $this->repositories
        );
    }

    public function hasRepository(string $name): bool {
        foreach ($this->repositories as $repository) {
            if ($repository->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $name
     * @return Repository
     * @throws \OutOfBoundsException if no {@link Repository} with specified name is found
     */
    public function getRepository(string $name): Repository {
        foreach ($this->repositories as $repository) {
            if ($repository->getName() === $name) {
                return $repository;
            }
        }

        throw new \OutOfBoundsException("No repository with name '$name'");
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

        $this->repositories[] = $repository;
        if ($repository->getWorkingTree() !== $this) {
            $repository->setWorkingTree($this);
        }
    }

    public function removeRepository(string $name): ?Repository {
        foreach ($this->repositories as $i => $repository) {
            if ($repository->getName() === $name) {
                array_splice($this->repositories, $i, 1);
                if ($repository->getWorkingTree() === $this) {
                    $repository->setWorkingTree(null);
                }

                return $repository;
            }
        }

        return null;
    }
}
