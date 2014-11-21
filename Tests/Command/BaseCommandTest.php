<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\Test\PageBundle\Command;

use Sonata\PageBundle\Command\BaseCommand;

/**
 * Class BaseCommandTest
 *
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 */
class BaseCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BaseCommand
     */
    private $command;

    /**
     * Sets up a new BaseCommand instance
     */
    public function setUp()
    {
        $this->command = $this->getMockBuilder('Sonata\PageBundle\Command\BaseCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('getSiteManager'))
            ->getMock();
    }

    /**
     * Tests the getSites() method with different parameters
     */
    public function testGetSites()
    {
        // Given
        $method = new \ReflectionMethod($this->command, 'getSites');
        $method->setAccessible(true);

        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');

        $siteManager = $this->getMockBuilder('Sonata\PageBundle\Entity\SiteManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->command->expects($this->any())->method('getSiteManager')->will($this->returnValue($siteManager));

        // Test --site=all value
        $input->expects($this->at(0))->method('getOption')->with('site')->will($this->returnValue(array('all')));
        $siteManager->expects($this->at(0))->method('findBy')->with(array());

        // Test --site=10 value
        $input->expects($this->at(1))->method('getOption')->with('site')->will($this->returnValue(array('10')));
        $siteManager->expects($this->at(1))->method('findBy')->with(array('id' => 10));

        // Test --site=10 --site=11 value
        $input->expects($this->at(2))->method('getOption')->with('site')->will($this->returnValue(array('10', '11')));
        $siteManager->expects($this->at(2))->method('findBy')->with(array('id' => array(10, 11)));

        $method->invoke($this->command, $input);
        $method->invoke($this->command, $input);
        $method->invoke($this->command, $input);
    }
}