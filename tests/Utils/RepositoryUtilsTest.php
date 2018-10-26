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

/**
 * @coversDefaultClass \OneGuard\DockerBuildOrchestrator\Utils\RepositoryUtils
 */
class RepositoryUtilsTest extends TestCase {
    /**
     * @param string $name1
     * @param string $name2
     * @param int $result
     *
     * @dataProvider fullNameProducer
     * @covers ::fullNameComparator
     */
    public function testFullNameComparator(string $name1, string $name2, int $result) {
        $this->assertEquals($result, RepositoryUtils::fullNameComparator($name1, $name2));
    }

    /**
     * @param string $registry1
     * @param string $registry2
     * @param int $result
     *
     * @dataProvider registryProducer
     * @covers ::registryComparator
     */
    public function testRegistryComparator(string $registry1, string $registry2, int $result) {
        $this->assertEquals($result, RepositoryUtils::registryComparator($registry1, $registry2));
    }

    /**
     * @param string $fqdn1
     * @param string $fqdn2
     * @param int $result
     *
     * @dataProvider fqdnProducer
     * @covers ::fqdnComparator
     */
    public function testFqdnComparator(string $fqdn1, string $fqdn2, int $result) {
        $this->assertEquals($result, RepositoryUtils::fqdnComparator($fqdn1, $fqdn2));
    }

    /**
     * @covers ::generateFullName
     */
    public function testGenerateFullName() {
        $this->assertEquals(
            'test.docker.io/tester/test',
            RepositoryUtils::generateFullName('test', 'tester', 'test.docker.io')
        );
    }

    public static function fullNameProducer() {
        return [
            ['test', 'test', 0],
            ['a', 'b', -1],
            ['b', 'a', 1],
            ['a/test', 'b/test', -1],
            ['b/test', 'a/test', 1],
            ['test/a', 'test/b', -1],
            ['test/b', 'test/a', 1],
            ['docker.io/a/test', 'docker.io/b/test', -1],
            ['docker.io/b/test', 'docker.io/a/test', 1],
            ['docker1.io/a/test', 'docker2.io/a/test', -1],
            ['docker2.io/a/test', 'docker1.io/a/test', 1],
            ['test', 'docker.io/a/test', -2],
            ['docker.io/a/test', 'test', 2],
            ['a/test', 'docker.io/a/test', -1],
            ['docker.io/a/test', 'a/test', 1],
            ['test', 'a/test', -1],
            ['a/test', 'test', 1],
        ];
    }

    public static function registryProducer() {
        return [
            ['', '', 0],
            ['docker.io', 'docker.io', 0],
            ['docker.io:80', 'docker.io:80', 0],
            ['docker.io:80', 'docker.io', 1],
            ['docker.io', 'docker.io:80', -1],
            ['', 'docker.io', -1],
            ['docker.io', '', 1],
            ['', 'docker.io:80', -1],
            ['docker.io:80', '', 1],
            ['docker.io:80', 'docker.io:81', -1],
            ['docker.io:81', 'docker.io:80', 1]
        ];
    }

    public static function fqdnProducer() {
        return [
            ['example.com', 'example.com', 0],
            ['example.com', 'sub.example.com', 1],
            ['sub.example.com', 'example.com', -1],
            ['a.example.com', 'b.example.com', -1],
            ['b.example.com', 'a.example.com', 1],
            ['a.example2.com', 'a.example.com', 1],
            ['a.example.com', 'a.example2.com', -1],
            ['', '', 0],
            ['', 'example.com', -1],
            ['example.com', '', 1]
        ];
    }
}
