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

use PHPUnit\Framework\TestCase;

class VersionUtilsTest extends TestCase {
    /**
     * @var VersionUtils
     */
    private static $utils;

    public static function setUpBeforeClass() {
        self::$utils = new VersionUtils();
    }

    /**
     * @param string $version
     * @param string $expected
     *
     * @dataProvider versionProvider
     */
    public function testNormalize(string $version, string $expected) {
        $this->assertEquals($expected, self::$utils->normalize($version));
    }

    public function versionProvider() {
        return [
            ['1.2.3', '1.2.3'],
            ['1.2.3-alpha1', '1.2.3-alpha1'],
            ['1.2.3-alpha1-1-ae7fs8974a', '1.2.3-alpha1.1.ae7fs8974a'],
            ['1.2.3-rc', '1.2.3-rc'],
            ['1.2.3-rc.1', '1.2.3-rc.1']
        ];
    }
}
