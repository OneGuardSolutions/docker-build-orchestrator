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
use OneGuard\DockerBuildOrchestrator\Command\CheckCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\ListCommand;

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
        $this->assertCount(count($knownCommands), $app->all());
        $this->assertTrue($app->has('help'));
        $this->assertTrue($app->has('list'));
        foreach ($knownCommands as $name => $type) {
            $this->assertInstanceOf($type, $app->get($name));
        }
    }

    private static function knownCommands(): array {
        return [
            'build' => BuildCommand::class,
            'check' => CheckCommand::class,
            'help' => HelpCommand::class,
            'list' => ListCommand::class,
            'update' => UpdateCommand::class
        ];
    }
}
