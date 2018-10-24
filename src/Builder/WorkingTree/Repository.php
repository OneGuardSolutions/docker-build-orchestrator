<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Builder\WorkingTree;

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

        // unregister from old working tree
        if ($oldWorkingTree !== $workingTree && $oldWorkingTree !== null && $oldWorkingTree->hasRepository($this->name)) {
            $oldWorkingTree->removeRepository($this->name);
        }

        if ($workingTree === null) {
            return;
        }

        // register with new repository
        if (!$workingTree->hasRepository($this->name) || $workingTree->getRepository($this->name) !== $this) {
            $workingTree->addRepository($this);
        }
    }

    /**
     * @return string[]
     */
    public function getTagNames(): array {
        return array_map(
            function (Tag $tag) {
                return $tag->getName();
            },
            $this->tags
        );
    }

    public function hasTag(string $name): bool {
        foreach ($this->tags as $tag) {
            if ($tag->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $name
     * @return Tag
     * @throws \OutOfBoundsException if no {@link Tag} with specified name is found
     */
    public function getTag(string $name): Tag {
        foreach ($this->tags as $tag) {
            if ($tag->getName() === $name) {
                return $tag;
            }
        }

        throw new \OutOfBoundsException("No tag with name '$name'");
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

        $this->tags[] = $tag;
        if ($tag->getRepository() !== $this) {
            $tag->setRepository($this);
        }
    }

    public function removeTag(string $name): ?Tag {
        foreach ($this->tags as $i => $tag) {
            if ($tag->getName() === $name) {
                array_splice($this->tags, $i, 1);
                if ($tag->getRepository() === $this) {
                    $tag->setRepository(null);
                }

                return $tag;
            }
        }

        return null;
    }

    public function getFullName(): string {
        return (empty($this->registry) ? '' : $this->registry . '/') . $this->namespace . '/' . $this->name;
    }
}
