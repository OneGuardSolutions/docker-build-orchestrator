<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OneGuard\DockerBuildOrchestrator\Command;

use OneGuard\DockerBuildOrchestrator\Builder\NoDockerfileFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversDefaultClass \OneGuard\DockerBuildOrchestrator\Command\BuildCommand
 */
class BuildCommandTest extends TestCase {
    private const MSG = " - test.docker.io/test/test
     ↳ 1 → 1.2 (1.2.3)
     ↳ 1-dev → 1.2-dev (1.2.3-dev)
     ↳ 1.2 → 1.2.3
     ↳ 1.2-dev → 1.2.3-dev
     ↳ 1.2.3 → tests/_resources/docker/repositories-1/test/1.2.3/Dockerfile
     ↳ 1.2.3-dev → tests/_resources/docker/repositories-1/test/1.2.3-dev/Dockerfile
     ↳ dev → 1-dev (1.2.3-dev)
     ↳ latest → 1 (1.2.3)
 - test.docker.io/test/test-2
     ↳ 1 → tests/_resources/docker/repositories-1/test-2/1/Dockerfile
 - test-2.docker.io/test/test
     ↳ 1 → tests/_resources/docker/repositories-2/test/1/Dockerfile
     ↳ 2 → 2.0
     ↳ edge → 2 (unresolved)
     ↳ latest → 1
";

    /**
     * @covers ::configure
     */
    public function testConfigure() {
        $command = new BuildCommand();

        $this->assertEquals('build', $command->getName());
        $this->assertEquals('Builds docker images', $command->getDescription());
        $this->assertTrue($command->getDefinition()->hasOption('directory'));
    }

    /**
     * @covers ::execute
     */
    public function testExecute() {
        $command = new BuildCommand();
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--directory' => [
                __DIR__ . '/../_resources/docker/repositories-1',
                __DIR__ . '/../_resources/docker/repositories-2'
            ]
        ]);
        $output = $commandTester->getDisplay();

        $this->assertEquals(self::MSG, $this->relativePaths($output));
    }

    /**
     * @throws NoDockerfileFoundException
     *
     * @expectedException \OneGuard\DockerBuildOrchestrator\Builder\NoDockerfileFoundException
     * @covers ::execute
     */
    public function testExecuteCwd() {
        $command = new BuildCommand();
        $commandTester = new CommandTester($command);
        try {
            $commandTester->execute([]);
        } catch (NoDockerfileFoundException $e) {
            $this->assertEquals(
                "No root directories specified, adding current working directory.\n",
                $commandTester->getDisplay()
            );

            throw $e;
        }
    }

    private function relativePaths(string $content) {
        return preg_replace('/→ .*tests\/_resources\/docker\//', '→ tests/_resources/docker/', $content);
    }
}
