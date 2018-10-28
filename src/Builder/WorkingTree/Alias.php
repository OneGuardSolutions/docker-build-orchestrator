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

class Alias extends Tag {
    /**
     * @var string
     */
    protected $reference;

    public function __construct(string $name, string $reference) {
        parent::__construct($name);
        $this->reference = $reference;
    }

    /**
     * @return string
     */
    public function getReference(): string {
        return $this->reference;
    }

    public function resolve(): ?Tag {
        $tag = $this;
        while ($tag instanceof Alias) {
            $tag = $tag->isBroken() ? null : $tag->getRepository()->getTag($tag->getReference());
        }

        return $tag;
    }

    public function isBroken(): bool {
        return $this->repository === null || !$this->repository->hasTag($this->reference);
    }
}
