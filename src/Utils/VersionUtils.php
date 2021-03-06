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

class VersionUtils {
    /**
     * @param string $version
     * @return string
     */
    public static function normalize(string $version) {
        $index = strpos($version, '-');
        if ($index === false) {
            return $version;
        }

        return substr($version, 0, $index + 1)
            . preg_replace('/[^a-zA-Z0-9]/', '.', substr($version, $index + 1));
    }
}
