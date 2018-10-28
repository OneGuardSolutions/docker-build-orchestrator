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

use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Alias;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\NamedImage;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Repository;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\WorkingTree;
use OneGuard\DockerBuildOrchestrator\Utils\DockerfileUtils;
use OneGuard\DockerBuildOrchestrator\Utils\RepositoryUtils;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class Builder {
    /**
     * @param string[]|\SplFileInfo[] $rootDirs
     * @return WorkingTree
     */
    public function buildAll(array $rootDirs): WorkingTree {
        if (empty($rootDirs)) {
            throw new \InvalidArgumentException('No root directory');
        }

        $dockerFiles = array_merge(...array_map(
            function ($rootDir) {
                if ($rootDir instanceof \SplFileInfo) {
                    $rootDir = $rootDir->getPathname();
                } elseif (!is_string($rootDir)) {
                    throw new \InvalidArgumentException(
                        'Expected string or SplFileInfo, got ' . gettype($rootDir)
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

        return $this->buildWorkingTree($dockerFiles);
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

    /**
     * @param string[] $dockerFiles
     * @return WorkingTree
     */
    public function buildWorkingTree(array $dockerFiles): WorkingTree {
        $workingTree = new WorkingTree();
        foreach ($dockerFiles as $dockerFile) {
            [$repositoryDirectory, $repositoryName, $tagName] = DockerfileUtils::parseRepositoryAndTagName($dockerFile);
            $repository = null;
            $configuration = $this->parseConfigFile($repositoryDirectory);
            $repositoryFullName = RepositoryUtils::generateFullName(
                $repositoryName,
                $configuration['namespace'],
                $configuration['registry']
            );
            if ($workingTree->hasRepository($repositoryFullName)) {
                $repository = $workingTree->getRepository($repositoryFullName);
            } else {
                $repository = new Repository($repositoryName, $configuration['namespace'], $configuration['registry']);
                foreach ($configuration['aliases'] as $alias => $reference) {
                    $repository->addTag(new Alias($alias, $reference));
                }
                $workingTree->addRepository($repository);
            }

            $tag = new NamedImage($tagName, $dockerFile);
            $repository->addTag($tag);
        }

        return $workingTree;
    }

    public function parseConfigFile(string $repositoryDirectory): array {
        $configuration = [
            'registry' => '',
            'namespace' => 'library',
            'aliases' => []
        ];
        $configFile = null;
        if (is_file($repositoryDirectory . '/repository.yaml')) {
            $configFile = $repositoryDirectory . '/repository.yaml';
        } elseif (is_file($repositoryDirectory . '/repository.yml')) {
            $configFile = $repositoryDirectory . '/repository.yml';
        }

        if ($configFile !== null) {
            $configuration = array_merge($configuration, Yaml::parseFile($configFile));
        }

        return $configuration;
    }
}
