<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OneGuard\DockerBuildOrchestrator\Builder;

use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase {
    public function testBuildAll() {
        $builder = new Builder();
        $workingTree = $builder->buildAll([
            __DIR__ . '/../_resources/docker/repositories-1',
            new \SplFileInfo(__DIR__ . '/../_resources/docker/repositories-2')
        ]);

        $this->assertCount(3, $workingTree->getRepositoryNames());

        $repository = $workingTree->getRepository('test');
        $this->assertEquals('test.docker.io/test/test', $repository->getFullName());
        $this->assertCount(8, $repository->getTagNames());

        $repository = $workingTree->getRepository('test-2');
        $this->assertEquals('test.docker.io/test/test-2', $repository->getFullName());
        $this->assertCount(1, $repository->getTagNames());

        $repository = $workingTree->getRepository('test-3');
        $this->assertEquals('test-2.docker.io/test/test-3', $repository->getFullName());
        $this->assertCount(4, $repository->getTagNames());
    }

    /**
     * @expectedException \OneGuard\DockerBuildOrchestrator\Builder\NoDockerfileFoundException
     */
    public function testBuildAllNoDockerfile() {
        $builder = new Builder();
        $builder->buildAll([__DIR__]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No root directory
     */
    public function testBuildAllNoRootDir() {
        $builder = new Builder();
        $builder->buildAll([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected string or SplFileInfo, got integer
     */
    public function testBuildAllNotPathNorSplFileInfo() {
        $builder = new Builder();
        $builder->buildAll([1]);
    }

    public function testFindDockerFiles() {
        $builder = new Builder();
        $root = realpath(__DIR__ . '/../_resources/docker/repositories-1');
        $dockerfilePaths = array_map(
            function (string $path) use ($root) {
                return substr(realpath($path), strlen($root) + 1);
            },
            $builder->findDockerFiles($root)
        );
        sort($dockerfilePaths);

        $this->assertEquals(
            [
                'test-2/1/Dockerfile',
                'test/1.2.3-dev/Dockerfile',
                'test/1.2.3/Dockerfile'
            ],
            $dockerfilePaths
        );
    }

    /**
     * @expectedException \OneGuard\DockerBuildOrchestrator\Builder\DirectoryNotFoundException
     * @expectedExceptionMessage Directory '/not/exist' not found
     */
    public function testFindDockerFilesRootNotExist() {
        $builder = new Builder();
        $builder->findDockerFiles('/not/exist');
    }

    public function testParseConfigFileDefaults() {
        $builder = new Builder();
        $this->assertEquals(
            [
                'registry' => '',
                'namespace' => 'library',
                'aliases' => []
            ],
            $builder->parseConfigFile(__DIR__)
        );
    }

    public function testParseConfigFileYaml() {
        $builder = new Builder();
        $this->assertEquals(
            [
                'registry' => 'test.docker.io',
                'namespace' => 'test',
                'aliases' => [
                    'latest' => '1',
                    'dev' => '1-dev',
                    '1' => '1.2',
                    '1.2' => '1.2.3',
                    '1-dev' => '1.2-dev',
                    '1.2-dev' => '1.2.3-dev'
                ]
            ],
            $builder->parseConfigFile(__DIR__ . '/../_resources/docker/repositories-1/test')
        );
    }

    public function testParseConfigFileYml() {
        $builder = new Builder();
        $this->assertEquals(
            [
                'registry' => 'test.docker.io',
                'namespace' => 'test',
                'aliases' => []
            ],
            $builder->parseConfigFile(__DIR__ . '/../_resources/docker/repositories-1/test-2')
        );
    }

    public function testBuildWorkingTree() {
        $builder = new Builder();
        $root = __DIR__ . '/../_resources/docker/repositories-1';
        $workingTree = $builder->buildWorkingTree([
            $root . '/test/1.2.3/Dockerfile',
            $root . '/test/1.2.3-dev/Dockerfile'
        ]);

        $this->assertCount(1, $workingTree->getRepositoryNames());

        $repository = $workingTree->getRepository('test');
        $this->assertEquals('test.docker.io/test/test', $repository->getFullName());
        $this->assertCount(8, $repository->getTagNames());

        $this->assertEquals('1.2', $repository->getTag('1')->getReference());
        $this->assertEquals('1.2-dev', $repository->getTag('1-dev')->getReference());
        $this->assertEquals('1.2.3', $repository->getTag('1.2')->getReference());
        $this->assertEquals('1.2.3-dev', $repository->getTag('1.2-dev')->getReference());
        $this->assertEquals(
            'test/1.2.3/Dockerfile',
            substr($repository->getTag('1.2.3')->getDockerfilePath(), strlen($root) + 1)
        );
        $this->assertEquals(
            'test/1.2.3-dev/Dockerfile',
            substr($repository->getTag('1.2.3-dev')->getDockerfilePath(), strlen($root) + 1)
        );
        $this->assertEquals('1-dev', $repository->getTag('dev')->getReference());
        $this->assertEquals('1', $repository->getTag('latest')->getReference());
    }
}
