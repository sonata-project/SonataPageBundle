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

namespace Sonata\PageBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Command\BaseCommand;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 */
final class BaseCommandTest extends TestCase
{
    /** @var BaseCommand */
    private $command;

    /** @var ServiceLocator */
    private $locator;

    /**
     * Sets up a new BaseCommand instance.
     */
    protected function setUp(): void
    {
        $this->locator = $this->createMock(ServiceLocator::class);
        $this->command = $this->getMockForAbstractClass(BaseCommand::class, [$this->locator]);
    }

    /**
     * Tests the getSites() method with different parameters.
     */
    public function testGetSites(): void
    {
        $input = $this->createMock(InputInterface::class);

        $method = new \ReflectionMethod($this->command, 'getSites');
        $method->setAccessible(true);

        $input->expects(static::exactly(3))->method('getOption')->with('site')->willReturnOnConsecutiveCalls(
            ['all'],
            ['10'],
            ['10', '11']
        );

        $siteManager = $this->createMock(SiteManagerInterface::class);

        $this->locator
            ->expects(static::exactly(3))
            ->method('__invoke')
            ->with('sonata.page.manager.site')
            ->willReturn($siteManager);

        $method->invoke($this->command, $input);
        $method->invoke($this->command, $input);
        $method->invoke($this->command, $input);
    }
}
