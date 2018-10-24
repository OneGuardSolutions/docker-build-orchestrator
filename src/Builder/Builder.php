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

use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Repository;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Tag;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Visitor\ConsoleOutputVisitor;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\WorkingTree;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class Builder {
    /**
     * @param string[]|\SplFileInfo[] $rootDirs
     */
    public function buildAll(array $rootDirs): void {
        $dockerFiles = array_merge(...array_map(
            function ($rootDir) {
                if ($rootDir instanceof \SplFileInfo) {
                    $rootDir = $rootDir->getPathname();
                } else if (!is_string($rootDir)) {
                    throw new \InvalidArgumentException(
                        'expected string or SplFileInfo, got ' . gettype($rootDir)
                    );
                }

                return $this->findDockerFiles($rootDir);
            },
            $rootDirs
        ));

        if (empty($dockerFiles)) {
            throw new NoDockerfileFoundException();
        }

        sort($dockerFiles);
        $dockerFiles = array_unique($dockerFiles);
        $workingTree = $this->buildWorkingTree($dockerFiles);

        (new ConsoleOutputVisitor())->visitWorkingTree($workingTree);
    }

    /**
     * @param string $rootDir
     * @return string[]
     */
    public function findDockerFiles(string $rootDir): array {
        if (!is_dir($rootDir)) {
            throw new DirectoryNotFoundException($rootDir);
        }

        $finder = (new Finder())->files()->in($rootDir . '/*/*')->name('Dockerfile')->depth(0);
        $files = [];
        foreach ($finder->getIterator() as $fileInfo) {
            $files[] = $fileInfo->getRealPath();
        }

        return $files;
    }

    private function parseRepositoryAndTagName(string $dockerFilePath) {
        $dockerFileDirectory = dirname($dockerFilePath);
        $tagName = basename($dockerFileDirectory);
        $repositoryDirectory = dirname($dockerFileDirectory);
        $repositoryName = basename(dirname($dockerFileDirectory));

        return [$repositoryDirectory, $repositoryName, $tagName];
    }

    /**
     * @param string[] $dockerFiles
     * @return WorkingTree
     */
    public function buildWorkingTree(array $dockerFiles): WorkingTree {
        $workingTree = new WorkingTree();
        foreach ($dockerFiles as $dockerFile) {
            [$repositoryDirectory, $repositoryName, $tagName] = $this->parseRepositoryAndTagName($dockerFile);
            $configuration = [
                'registry' => '',
                'namespace' => 'library',
                'aliases' => []
            ];
            if (is_file($repositoryDirectory . '/repository.yaml')) {
                $newConf = Yaml::parseFile($repositoryDirectory . '/repository.yaml');
                $configuration = array_merge($configuration, $newConf);
            }
            $repository = null;
            if ($workingTree->hasRepository($repositoryName)) {
                $repository = $workingTree->getRepository($repositoryName);
            } else {
                $repository = new Repository($repositoryName, $configuration['namespace'], $configuration['registry']);
                $workingTree->addRepository($repository);
            }

            $tag = new Tag($tagName);
            $repository->addTag($tag);
        }

        return $workingTree;
    }
}
