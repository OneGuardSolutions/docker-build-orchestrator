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

abstract class Tag {
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Repository|null
     */
    protected $repository;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getRepository(): ?Repository {
        return $this->repository;
    }

    public function setRepository(?Repository $repository): void {
        $oldRepository = $this->repository;
        $this->repository = $repository;

        // unregister from old repository
        if ($oldRepository !== $repository && $oldRepository !== null && $oldRepository->hasTag($this->name)) {
            $oldRepository->removeTag($this->name);
        }

        if ($repository === null) {
            return;
        }

        // register with new repository
        if (!$repository->hasTag($this->name) || $repository->getTag($this->name) !== $this) {
            $repository->addTag($this);
        }
    }

    public function getFullName(): string {
        return $this->repository ? $this->repository->getFullName() . ':' . $this->name : $this->name;
    }
}
