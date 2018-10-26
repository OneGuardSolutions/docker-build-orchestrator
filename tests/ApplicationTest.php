<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OneGuard\DockerBuildOrchestrator;

use Deployer\Component\PharUpdate\Console\Command as UpdateCommand;
use OneGuard\DockerBuildOrchestrator\Command\BuildCommand;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \OneGuard\DockerBuildOrchestrator\Application
 */
class ApplicationTest extends TestCase {
    /**
     * @covers ::__construct
     */
    public function testConstruct() {
        $app = new Application('test');

        $this->assertEquals('dobr', $app->getName());
        $this->assertEquals('test', $app->getVersion());

        $knownCommands = self::knownCommands();

        /** @noinspection PhpParamsInspection */
        $this->assertCount(2 + count($knownCommands), $app->all());
        $this->assertTrue($app->has('help'));
        $this->assertTrue($app->has('list'));
        foreach ($knownCommands as $name => $type) {
            $this->assertInstanceOf($type, $app->get($name));
        }
    }

    private static function knownCommands(): array {
        return [
            'build' => BuildCommand::class,
            'update' => UpdateCommand::class
        ];
    }
}
