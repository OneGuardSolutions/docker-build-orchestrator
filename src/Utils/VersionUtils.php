<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Utils;

class VersionUtils {
    /**
     * @param string $version
     * @return string
     */
    public function normalize(string $version) {
        $i = strpos($version, '-');
        if ($i === false) {
            return $version;
        }

        return substr($version, 0, $i + 1)
            . preg_replace('/[^a-zA-Z0-9]/', '.', substr($version, $i + 1));
    }
}
