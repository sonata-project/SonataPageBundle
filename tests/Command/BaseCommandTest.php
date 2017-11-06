<?php

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

/**
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 */
class BaseCommandTest extends TestCase
{
    /**
     * @var BaseCommand
     */
    private $command;

    /**
     * Sets up a new BaseCommand instance.
     */
    public function setUp()
    {
        $this->command = $this->getMockBuilder('Sonata\PageBundle\Command\BaseCommand')
            ->disableOriginalConstructor()
            ->setMethods(['getSiteManager'])
            ->getMock();
    }

    /**
     * Tests the getSites() method with different parameters.
     */
    public function testGetSites()
    {
        // Given
        $method = new \ReflectionMethod($this->command, 'getSites');
        $method->setAccessible(true);

        $input = $this->createMock('Symfony\Component\Console\Input\InputInterface');

        $siteManager = $this->getMockBuilder('Sonata\PageBundle\Entity\SiteManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->command->expects($this->any())->method('getSiteManager')->will($this->returnValue($siteManager));

        // Test --site=all value
        $input->expects($this->at(0))->method('getOption')->with('site')->will($this->returnValue(['all']));
        $siteManager->expects($this->at(0))->method('findBy')->with([]);

        // Test --site=10 value
        $input->expects($this->at(1))->method('getOption')->with('site')->will($this->returnValue(['10']));
        $siteManager->expects($this->at(1))->method('findBy')->with(['id' => 10]);

        // Test --site=10 --site=11 value
        $input->expects($this->at(2))->method('getOption')->with('site')->will($this->returnValue(['10', '11']));
        $siteManager->expects($this->at(2))->method('findBy')->with(['id' => [10, 11]]);

        $method->invoke($this->command, $input);
        $method->invoke($this->command, $input);
        $method->invoke($this->command, $input);
    }
}
