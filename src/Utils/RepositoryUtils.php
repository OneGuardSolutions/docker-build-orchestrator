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

class RepositoryUtils {
    public static function fullNameComparator(string $name1, string $name2): int {
        $parts1 = explode('/', $name1, 3);
        $parts2 = explode('/', $name2, 3);

        if (count($parts1) !== count($parts2)) {
            return count($parts1) - count($parts2);
        }
        if (
            count($parts1) === 3 &&
            ($registry1 = array_shift($parts1)) !== ($registry2 = array_shift($parts2))
        ) {
            return self::registryComparator($registry1, $registry2);
        }
        if (count($parts1) === 2 && ($ns1 = array_shift($parts1)) !== ($ns2 = array_shift($parts2))) {
            return $ns1 < $ns2 ? -1 : 1;
        }

        return $parts1[0] === $parts2[0] ? 0 : ($parts1[0] < $parts2[0] ? -1 : 1);
    }

    public static function registryComparator(string $registryName1, string $registryName2): int {
        $parts1 = explode(':', $registryName1);
        $parts2 = explode(':', $registryName2);

        if ($parts1[0] !== $parts2[0]) {
            return self::fqdnComparator($parts1[0], $parts2[0]);
        }
        if (count($parts1) > 1 || count($parts2) > 1) {
            return count($parts1) === 1 ? -1 : (count($parts2) === 1 ? 1 : $parts1[1] - $parts2[1]);
        }

        return 0;
    }

    public static function fqdnComparator(string $fqdn1, string $fqdn2): int {
        $parts1 = explode('.', $fqdn1);
        $parts2 = explode('.', $fqdn2);

        do {
            $partLeft = array_pop($parts1);
            $partRight = array_pop($parts2);
            if ($partLeft !== $partRight) {
                return $partLeft < $partRight ? -1 : 1;
            }
        } while (!empty($parts1) && !empty($parts2));

        return !empty($parts1) ? -1 : (!empty($parts2) ? 1 : 0);
    }

    public static function generateFullName(
        string $name,
        string $namespace = 'library',
        string $registry = ''
    ): string {
        return (empty($registry) ? '' : $registry . '/') . $namespace . '/' . $name;
    }
}
