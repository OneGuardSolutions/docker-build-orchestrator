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

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversDefaultClass \OneGuard\DockerBuildOrchestrator\Command\CheckCommand
 */
class CheckCommandTest extends TestCase {
    private const MSG = " - test.docker.io/test/test
     ↳ 1 → 1.2 (1.2.3)
     ↳ 1-dev → 1.2-dev (1.2.3-dev)
     ↳ 1.2 → 1.2.3
     ↳ 1.2-dev → 1.2.3-dev
     ↳ 1.2.3 → tests/_resources/docker/repositories-1/test/1.2.3/Dockerfile
         - depends on: busybox:latest (external)
     ↳ 1.2.3-dev → tests/_resources/docker/repositories-1/test/1.2.3-dev/Dockerfile
         - depends on: busybox:latest (external)
         - depends on: test.docker.io/test/test:1.2.3
     ↳ dev → 1-dev (1.2.3-dev)
     ↳ latest → 1 (1.2.3)
 - test.docker.io/test/test-2
     ↳ 1 → tests/_resources/docker/repositories-1/test-2/1/Dockerfile
         - depends on: busybox:latest (external)

 [OK] Repositories are healthy.                                                 \n
";

    private const MSG_CYCLIC_ERROR = " - test-4.docker.io/test/test
     ↳ a → tests/_resources/docker/repositories-4/test/a/Dockerfile
         - depends on: test-4.docker.io/test/test:latest
     ↳ b → tests/_resources/docker/repositories-4/test/b/Dockerfile
         - depends on: test-4.docker.io/test/test:c
         - depends on: test-4.docker.io/test/test:a
     ↳ c → tests/_resources/docker/repositories-4/test/c/Dockerfile
         - depends on: test-4.docker.io/test/test:a
     ↳ latest → b

 [ERROR] Cyclic dependencies detected                                           \n
 1) test-4.docker.io/test/test:a
      → test-4.docker.io/test/test:b
      → test-4.docker.io/test/test:a

 2) test-4.docker.io/test/test:a
      → test-4.docker.io/test/test:b
      → test-4.docker.io/test/test:c
      → test-4.docker.io/test/test:a

";

    private const MSG_ALIASES_ERROR = " - test-2.docker.io/test/test
     ↳ 1 → tests/_resources/docker/repositories-2/test/1/Dockerfile
         - depends on: busybox:latest (external)
     ↳ 2 → 2.0
     ↳ edge → 2 (unresolved)
     ↳ latest → 1

 [ERROR] Broken aliases detected                                                \n
 1) test-2.docker.io/test/test:2 → 2.0

";

    /**
     * @covers ::configure
     */
    public function testConfigure() {
        $command = new CheckCommand();

        $this->assertEquals('check', $command->getName());
        $this->assertEquals(
            'Checks health status of detected repositories repositories and images',
            $command->getDescription()
        );
        $this->assertTrue($command->getDefinition()->hasOption('directory'));
    }

    /**
     * @covers ::execute
     */
    public function testExecute() {
        $command = new CheckCommand();
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--directory' => [__DIR__ . '/../_resources/docker/repositories-1']]);
        $output = $commandTester->getDisplay();

        $this->assertEquals(self::MSG, $this->relativePaths($output));
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    /**
     * @covers ::execute
     */
    public function testExecuteCwdWithNoRepositories() {
        $command = new CheckCommand();
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertEquals(
            "No root directories specified, adding current working directory.\nNo Docker images were found.\n",
            $commandTester->getDisplay()
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    /**
     * @covers ::execute
     * @covers ::checkForBrokenAliases
     */
    public function testExecuteBrokenAliases() {
        $command = new CheckCommand();
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--directory' => [__DIR__ . '/../_resources/docker/repositories-2']]);

        $this->assertEquals(self::MSG_ALIASES_ERROR, $this->relativePaths($commandTester->getDisplay()));
        $this->assertEquals(1, $commandTester->getStatusCode());
    }

    /**
     * @covers ::execute
     * @covers ::checkForCyclicDependencies
     */
    public function testExecuteCyclicDependencies() {
        $command = new CheckCommand();
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--directory' => [__DIR__ . '/../_resources/docker/repositories-4']]);

        $this->assertEquals(self::MSG_CYCLIC_ERROR, $this->relativePaths($commandTester->getDisplay()));
        $this->assertEquals(2, $commandTester->getStatusCode());
    }

    private function relativePaths(string $content) {
        return preg_replace('/→ .*tests\/_resources\/docker\//', '→ tests/_resources/docker/', $content);
    }
}
