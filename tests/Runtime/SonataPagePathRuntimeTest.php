<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Runtime;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Runtime\SonataPagePathRuntime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Runtime\Runner\Symfony\ConsoleApplicationRunner;
use Symfony\Component\Runtime\Runner\Symfony\HttpKernelRunner;

/**
 * @group legacy
 *
 * NEXT_MAJOR: Remove this class
 */
class SonataPagePathRuntimeTest extends TestCase
{
    public function testGetRunnerWithHttpKernel(): void
    {
        $kernelMock = $this->createMock(HttpKernelInterface::class);
        $runtime = new SonataPagePathRuntime();

        $runner = $runtime->getRunner($kernelMock);
        static::assertInstanceOf(HttpKernelRunner::class, $runner);
    }

    public function testGetRunnerWithNonHttpKernelApplication(): void
    {
        $command = new Command('app:test-cmd');
        $runtime = new SonataPagePathRuntime();

        $runner = $runtime->getRunner($command);
        static::assertInstanceOf(ConsoleApplicationRunner::class, $runner);
    }
}
