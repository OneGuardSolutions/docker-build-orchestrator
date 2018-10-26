<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Visitor;

use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Alias;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\NamedImage;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Repository;
use OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\TestTag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @coversDefaultClass \OneGuard\DockerBuildOrchestrator\Builder\WorkingTree\Visitor\ConsoleOutputVisitor
 */
class ConsoleOutputVisitorTest extends TestCase {
    /**
     * @var BufferedOutput
     */
    private $output;

    /**
     * @var ConsoleOutputVisitor
     */
    private $visitor;

    protected function setUp() {
        $this->output = new BufferedOutput();
        $this->visitor = new ConsoleOutputVisitor($this->output);
        $this->output->setDecorated(true);
    }

    /**
     * @covers ::beforeRepository
     */
    public function testBeforeRepository() {
        $repository = new Repository('test', 'tester', 'test.docker.io');
        $this->visitor->visitRepository($repository);

        $this->assertEquals(
            " - test.docker.io/tester/test\n",
            $this->relativePaths($this->output->fetch())
        );
    }

    /**
     * @covers ::visitTag
     */
    public function testVisitTagAlias() {
        /* @var MockObject|ConsoleOutputVisitor $visitor */
        $visitor = $this->getMockBuilder(ConsoleOutputVisitor::class)
            ->disableOriginalConstructor()
            ->setMethods(['visitAlias'])
            ->getMock();
        $tag = new Alias('latest', '1');

        $visitor->expects($this->once())
            ->method('visitAlias')
            ->with($this->equalTo($tag));

        $visitor->visitTag($tag);
    }

    /**
     * @covers ::visitTag
     */
    public function testVisitTagNamedImage() {
        /* @var MockObject|ConsoleOutputVisitor $visitor */
        $visitor = $this->getMockBuilder(ConsoleOutputVisitor::class)
            ->disableOriginalConstructor()
            ->setMethods(['visitNamedImage'])
            ->getMock();
        $tag = new NamedImage(
            '1',
            realpath(__DIR__ . '/../../../_resources/docker/repositories-1/test/1.2.3/Dockerfile')
        );

        $visitor->expects($this->once())
            ->method('visitNamedImage')
            ->with($this->equalTo($tag));

        $visitor->visitTag($tag);
    }

    /**
     * @covers ::visitTag
     */
    public function testVisitTagOtherType() {
        $tag = new TestTag('1');
        $this->visitor->visitTag($tag);

        $this->assertEquals(
            "     ↳ 1\n",
            $this->relativePaths($this->output->fetch())
        );
    }

    /**
     * @covers ::visitAlias
     */
    public function testVisitAliasToNamedImage() {
        $alias = new Alias('latest', '1');
        $namedImage = new NamedImage(
            '1',
            realpath(__DIR__ . '/../../../_resources/docker/repositories-1/test/1.2.3/Dockerfile')
        );
        $repository = new Repository('test');
        $repository->addTag($namedImage);
        $repository->addTag($alias);
        $this->visitor->visitAlias($alias);

        $this->assertEquals(
            "     ↳ \e[36mlatest\e[39m → \e[32m1\e[39m\n",
            $this->relativePaths($this->output->fetch())
        );
    }

    /**
     * @covers ::visitAlias
     */
    public function testVisitAliasToAlias() {
        $alias1 = new Alias('latest', '1');
        $alias2 = new Alias('1', '1.0');
        $namedImage = new NamedImage(
            '1.0',
            realpath(__DIR__ . '/../../../_resources/docker/repositories-1/test/1.2.3/Dockerfile')
        );
        $repository = new Repository('test');
        $repository->addTag($namedImage);
        $repository->addTag($alias2);
        $repository->addTag($alias1);
        $this->visitor->visitAlias($alias1);

        $this->assertEquals(
            "     ↳ \e[36mlatest\e[39m → \e[36m1\e[39m (\e[32m1.0\e[39m)\n",
            $this->relativePaths($this->output->fetch())
        );
    }

    /**
     * @covers ::visitAlias
     */
    public function testVisitAliasToAliasWithUnresolvedReference() {
        $alias1 = new Alias('latest', '1');
        $alias2 = new Alias('1', '1.0');
        $repository = new Repository('test');
        $repository->addTag($alias2);
        $repository->addTag($alias1);
        $this->visitor->visitAlias($alias1);

        $this->assertEquals(
            "     ↳ \e[36mlatest\e[39m → \e[36m1\e[39m (\e[31munresolved\e[39m)\n",
            $this->relativePaths($this->output->fetch())
        );
    }

    /**
     * @covers ::visitAlias
     */
    public function testVisitAliasUnresolvedReference() {
        $alias = new Alias('latest', '1');
        $repository = new Repository('test');
        $repository->addTag($alias);
        $this->visitor->visitAlias($alias);

        $this->assertEquals(
            "     ↳ \e[36mlatest\e[39m → \e[31m1\e[39m\n",
            $this->relativePaths($this->output->fetch())
        );
    }

    /**
     * @covers ::visitAlias
     */
    public function testVisitAliasNoRepository() {
        $alias = new Alias('latest', '1');
        $this->visitor->visitAlias($alias);

        $this->assertEquals(
            "     ↳ \e[36mlatest\e[39m → \e[31m1\e[39m\n",
            $this->relativePaths($this->output->fetch())
        );
    }

    /**
     * @covers ::__construct
     * @covers ::visitNamedImage
     */
    public function testVisitNamedImage() {
        $namedImage = new NamedImage(
            '1.0.0',
            realpath(__DIR__ . '/../../../_resources/docker/repositories-1/test/1.2.3/Dockerfile')
        );
        $this->visitor->visitNamedImage($namedImage);

        $this->assertEquals(
            "     ↳ \e[32m1.0.0\e[39m → tests/_resources/docker/repositories-1/test/1.2.3/Dockerfile\n",
            $this->relativePaths($this->output->fetch())
        );
    }

    /**
     * @covers ::visitNamedImage
     */
    public function testVisitNamedImageDockerfileNotExists() {
        $namedImage = new NamedImage(
            '1.0.0',
            realpath(__DIR__ . '/../../../_resources/docker/repositories-1/test/1.2.3/Dockerfile') . '.not.exists'
        );
        $this->visitor->visitNamedImage($namedImage);

        $this->assertEquals(
            "     ↳ \e[32m1.0.0\e[39m → \e[31mtests/_resources/docker/repositories-1/test/1.2.3/Dockerfile.not.exists\e[39m\n",
            $this->relativePaths($this->output->fetch())
        );
    }

    private function relativePaths(string $content) {
        return preg_replace('/→ ([^\/]*)\/.*tests\/_resources\/docker\//', '→ $1tests/_resources/docker/', $content);
    }
}
