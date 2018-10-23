<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Builder;

use PHPUnit\Framework\TestCase;

class DirectoryNotFoundExceptionTest extends TestCase {
    public function testGetPath() {
        $exception = new DirectoryNotFoundException('/test');

        $this->assertEquals('/test', $exception->getPath());
    }
}
