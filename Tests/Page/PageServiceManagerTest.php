<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Page;

use Sonata\PageBundle\Page\PageServiceManager;

/**
 * Test the page service manager
 */
class PageServiceManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $router;

    /**
     * @var PageServiceManager
     */
    protected $manager;

    /**
     * setup each test
     */
    public function setUp()
    {
        $this->router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $this->manager = new PageServiceManager($this->router);
    }

    /**
     * Test adding a new page service
     */
    public function testAdd()
    {
        // GIVEN
        $service = $this->getMock('Sonata\PageBundle\Page\Service\PageServiceInterface');

        // WHEN
        $this->manager->add('default', $service);

        // THEN
        $this->assertEquals($service, $this->manager->get('default'));
    }

    /**
     * Test getting a service using a page object
     *
     * @depends testAdd
     */
    public function testGetByPage()
    {
        // GIVEN
        $service = $this->getMock('Sonata\PageBundle\Page\Service\PageServiceInterface');
        $this->manager->add('my-type', $service);

        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->once())->method('getType')->will($this->returnValue('my-type'));

        // WHEN
        $pageService = $this->manager->get($page);

        // THEN
        $this->assertSame($service, $pageService, 'Should return the page service');
    }

    /**
     * Test getting all page services
     *
     * @depends testAdd
     */
    public function testGetAll()
    {
        // GIVEN
        $service1 = $this->getMock('Sonata\PageBundle\Page\Service\PageServiceInterface');
        $service2 = $this->getMock('Sonata\PageBundle\Page\Service\PageServiceInterface');
        $this->manager->add('service1', $service1);
        $this->manager->add('service2', $service2);

        // WHEN
        $services = $this->manager->getAll();

        // THEN
        $this->assertEquals(array('service1' => $service1, 'service2' => $service2), $services, 'Should return all page services');
    }

    /**
     * Test the default page service
     *
     * @depends testAdd
     */
    public function testDefault()
    {
        // GIVEN
        $default = $this->getMock('Sonata\PageBundle\Page\Service\PageServiceInterface');
        $this->manager->setDefault($default);

        // WHEN
        $service = $this->manager->get('non-existing');

        // THEN
        $this->assertEquals($default, $service, 'Should return the default page service');
    }

    /**
     * Test the page execution through a service
     *
     * @depends testDefault
     */
    public function testExecute()
    {
        // GIVEN
        $request  = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalClone()
            ->getMock();
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->once())->method('getType')->will($this->returnValue('my-type'));

        $service = $this->getMock('Sonata\PageBundle\Page\Service\PageServiceInterface');
        $service->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($page), $this->equalTo($request))
            ->will($this->returnValue($response));
        $this->manager->add('my-type', $service);

        // WHEN
        $result = $this->manager->execute($page, $request);

        // THEN
        $this->assertSame($response, $result, 'Should return a response');
    }
}
