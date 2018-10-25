<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OneGuard\DockerBuildOrchestrator\Utils;

class DockerfileUtils {
    /**
     * Parses Dockerfile path and returns an array containing {@link Repository} root directory,
     * {@link Repository} name, and {@link Tag} name.
     *
     * @param string $dockerfilePath
     * @return string[]
     */
    public static function parseRepositoryAndTagName(string $dockerfilePath): array {
        $dockerfileDirectory = dirname($dockerfilePath);
        $tagName = basename($dockerfileDirectory);
        $repositoryDirectory = dirname($dockerfileDirectory);
        $repositoryName = basename(dirname($dockerfileDirectory));

        return [$repositoryDirectory, $repositoryName, $tagName];
    }
}
