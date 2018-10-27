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

use OneGuard\DockerBuildOrchestrator\Utils\RepositoryUtils;

class Repository {
    /**
     * @var string
     */
    private $registry;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $name;

    /**
     * @var WorkingTree|null
     */
    private $workingTree;

    /**
     * @var Tag[]
     */
    private $tags = [];

    public function __construct(string $name, string $namespace = 'library', string $registry = '') {
        $this->registry = $registry;
        $this->namespace = $namespace;
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getRegistry(): string {
        return $this->registry;
    }

    /**
     * @return string
     */
    public function getNamespace(): string {
        return $this->namespace;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getWorkingTree(): ?WorkingTree {
        return $this->workingTree;
    }

    public function setWorkingTree(?WorkingTree $workingTree): void {
        $oldWorkingTree = $this->workingTree;
        $this->workingTree = $workingTree;

        $fullName = $this->getFullName();
        // unregister from old working tree
        if ($oldWorkingTree !== $workingTree && $oldWorkingTree !== null && $oldWorkingTree->hasRepository($fullName)) {
            $oldWorkingTree->removeRepository($fullName);
        }

        if ($workingTree === null) {
            return;
        }

        // register with new repository
        if (!$workingTree->hasRepository($fullName) || $workingTree->getRepository($fullName) !== $this) {
            $workingTree->addRepository($this);
        }
    }

    /**
     * @return Tag[]
     */
    public function getTags(): array {
        return array_values($this->tags);
    }

    /**
     * @return string[]
     */
    public function getTagNames(): array {
        return array_keys($this->tags);
    }

    public function hasTag(string $name): bool {
        return !empty($this->tags[$name]);
    }

    /**
     * @param string $name
     * @return Tag
     * @throws \OutOfBoundsException if no {@link Tag} with specified name is found
     */
    public function getTag(string $name): Tag {
        if (!$this->hasTag($name)) {
            throw new \OutOfBoundsException("No tag with name '$name'");
        }

        return $this->tags[$name];
    }

    /**
     * Register the {@link Tag} with the {@link WorkingTree} if a {@link Tag} with the same name is not registered yet.
     *
     * @param Tag $tag
     * @throws \InvalidArgumentException
     *              if a {@link Tag} with same name is already registered in the {@link WorkingTree}
     */
    public function addTag(Tag $tag): void {
        $name = $tag->getName();
        if ($this->hasTag($name)) {
            throw new \InvalidArgumentException("Tag with name '$name' already exists");
        }

        $this->tags[$name] = $tag;
        if ($tag->getRepository() !== $this) {
            $tag->setRepository($this);
        }
    }

    public function removeTag(string $name): ?Tag {
        if (!$this->hasTag($name)) {
            return null;
        }

        $tag = $this->tags[$name];
        unset($this->tags[$name]);
        if ($tag->getRepository() === $this) {
            $tag->setRepository(null);
        }

        return $tag;
    }

    public function getFullName(): string {
        return RepositoryUtils::generateFullName($this->name, $this->namespace, $this->registry);
    }
}
