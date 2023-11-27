<?php

declare(strict_types=1);

namespace Sonata\PageBundle\Tests\Runtime;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Runtime\SonataPageRuntime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Runtime\Runner\Symfony\ConsoleApplicationRunner;
use Symfony\Component\Runtime\Runner\Symfony\HttpKernelRunner;

class SonataPageRuntimeTest extends TestCase
{

    public function testConstructorThrowsExceptionWithoutMultisite(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SonataPageRuntime();
    }

    public function testGetRunnerWithHttpKernel(): void
    {
        $kernelMock = $this->createMock(HttpKernelInterface::class);
        $runtime = new SonataPageRuntime(['multisite' => 'host_with_path_by_locale']);

        $runner = $runtime->getRunner($kernelMock);
        static::assertInstanceOf(HttpKernelRunner::class, $runner);
    }

    public function testGetRunnerWithNonHttpKernelApplication(): void
    {
        $command = new Command('app:test-cmd');
        $runtime = new SonataPageRuntime(['multisite' => 'host_with_path_by_locale']);

        $runner = $runtime->getRunner($command);
        static::assertInstanceOf(ConsoleApplicationRunner::class, $runner);
    }
}
