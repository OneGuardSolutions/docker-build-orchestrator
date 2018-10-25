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

class DockerfileUtilsTest extends TestCase {
    public function testParseRepositoryAndTagName() {
        $this->assertEquals(
            ['root/test', 'test', '1'],
            DockerfileUtils::parseRepositoryAndTagName('root/test/1/Dockerfile')
        );
    }
}
